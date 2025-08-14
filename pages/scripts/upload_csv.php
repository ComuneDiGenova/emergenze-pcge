<?php
session_start();

/**
 * === CONFIG LOG ===
 */
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
$logFile = $logDir . '/upload_csv_error_log.txt';

function logError($message) {
    global $logFile;
    $line = "[" . date("Y-m-d H:i:s") . "] $message\n";
    @file_put_contents($logFile, $line, FILE_APPEND);
    // anche su error_log del webserver per sicurezza
    error_log($line);
}

/**
 * === CONNESSIONE DB ===
 */
$conn_path = explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
if (!file_exists($conn_path)) {
    logError("File di connessione non trovato: $conn_path");
    die("Errore: file di connessione non trovato.");
}
include $conn_path;

if (!isset($conn) || !$conn) {
    $pgerr = function_exists('pg_last_error') ? pg_last_error() : 'n/a';
    logError("Connessione al database non valida. pg_last_error: $pgerr");
    die("Errore di connessione al database.");
}

@pg_set_client_encoding($conn, 'UTF8');

/**
 * === HELPERS CF ===
 */
function normalize_cf(string $raw): string {
    $s = strtoupper($raw);
    $s = preg_replace('/^\xEF\xBB\xBF/', '', $s); // BOM
    $s = preg_replace('/[^A-Z0-9]/', '', $s);     // solo alfanumerico
    return $s;
}
function is_valid_cf_basic(string $cf): bool {
    return (bool)preg_match('/^[A-Z0-9]{16}$/', $cf);
}

/**
 * === CARICAMENTO CSV ===
 */
if (!isset($_FILES['csvFile']) || $_FILES['csvFile']['error'] !== UPLOAD_ERR_OK) {
    echo "Errore nel caricamento del file. Riprova.";
    exit;
}

$csvFile       = $_FILES['csvFile']['tmp_name'];
$fileName      = $_FILES['csvFile']['name'];
$fileType      = $_FILES['csvFile']['type'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

$allowedMimeTypes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
if (!in_array($fileType, $allowedMimeTypes) || $fileExtension !== 'csv') {
    die("Errore: il file caricato non è un CSV valido. Carica un file con estensione .csv.");
}

// Legge l'intero file e converte in UTF-8
$raw = @file_get_contents($csvFile);
if ($raw === false) {
    die("Errore durante l'apertura del file.");
}

$enc = mb_detect_encoding($raw, ['UTF-8','Windows-1252','ISO-8859-1','ISO-8859-15'], true);
if ($enc === false) $enc = 'Windows-1252';

if ($enc !== 'UTF-8') {
    $converted = @mb_convert_encoding($raw, 'UTF-8', $enc);
    if ($converted === false) {
        $converted = @iconv($enc, 'UTF-8//IGNORE', $raw);
    }
    if ($converted === false) {
        logError("Conversione encoding fallita da $enc a UTF-8");
        die("Impossibile convertire il file in UTF-8.");
    }
    $raw = $converted;
}

// Normalizza CRLF ed elimina control chars (eccetto TAB/CR/LF)
$raw = str_replace("\r\n", "\n", $raw);
$raw = preg_replace('/[^\P{C}\t\n\r]/u', '', $raw);

// Stream temporaneo per fgetcsv
$handle = fopen('php://temp', 'r+');
fwrite($handle, $raw);
rewind($handle);

// Intestazione
$headers = fgetcsv($handle, 0, ";");
if ($headers === false || count($headers) === 0) {
    fclose($handle);
    die("CSV vuoto o intestazione non valida.");
}
if (isset($headers[0])) {
    $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
}

// Colonne richieste
$required_columns = ['CF', 'nome', 'cognome', 'data_nascita', 'comune', 'telefono', 'mail'];
foreach ($required_columns as $column) {
    if (!in_array($column, $headers, true)) {
        fclose($handle);
        echo "Colonna richiesta mancante: $column.";
        exit;
    }
}

/**
 * === IMPORT ===
 */
$imported           = 0;
$updated            = 0;
$records_read       = 0;
$skipped_invalid_cf = 0; // nuove metriche
$skipped_bad_row    = 0;

$cf_by_unit = [];   // "id1|id2" => [ CF_normalizzati ]

pg_query($conn, 'BEGIN');

try {
    $line_no = 1; // la riga header è 1
    while (($data = fgetcsv($handle, 0, ";")) !== false) {
        $line_no++;

        // Righe vuote o con numero di colonne errato
        if (empty($data) || count($data) !== count($headers)) {
            $skipped_bad_row++;
            // Logga dettaglio riga malformata
            $preview = json_encode($data, JSON_UNESCAPED_UNICODE);
            logError("Riga malformata/skippata alla riga #$line_no: colonne=" . count($data) . " (attese " . count($headers) . "). Dati=$preview");
            continue;
        }

        $records_read++;

        // Mappa header -> valori
        $row = array_combine($headers, $data);

        // CF
        $cf_raw = $row['CF'] ?? '';
        $cf     = normalize_cf($cf_raw);

        if (!is_valid_cf_basic($cf)) {
            $skipped_invalid_cf++;
            logError("CF non valido (basic). Riga #$line_no. Raw='{$cf_raw}' -> Normalized='{$cf}'.");
            continue; // scarta
        }

        // Campi base
        $nome         = trim($row['nome'] ?? '');
        $cognome      = trim($row['cognome'] ?? '');
        $data_nascita = trim($row['data_nascita'] ?? ''); // varchar in tabella
        $comune       = trim($row['comune'] ?? '');

        // Opzionali
        $cap       = ($row['cap'] ?? '') === '' ? null : trim($row['cap']);
        $indirizzo = ($row['indirizzo'] ?? '') === '' ? null : trim($row['indirizzo']);
        $telefono  = ($row['telefono'] ?? '') === '' ? null : trim($row['telefono']);
        $mail      = ($row['mail'] ?? '') === '' ? null : trim($row['mail']);
        $id1       = ($row['unita_operativa_I_liv'] ?? '') === '' ? null : trim($row['unita_operativa_I_liv']);
        $id2       = ($row['unita_operativa_II_liv'] ?? '') === '' ? null : trim($row['unita_operativa_II_liv']);
        $numero_gg = ($row['Numero_tessera'] ?? '') === '' ? null : trim($row['Numero_tessera']);

        // Accumula per delete per unità
        if ($id1 !== null && $id2 !== null) {
            $unit_key = "{$id1}|{$id2}";
            if (!isset($cf_by_unit[$unit_key])) {
                $cf_by_unit[$unit_key] = [];
            }
            $cf_by_unit[$unit_key][] = $cf;
        }

        // UPSERT utenti_esterni
        $query_ue = "
            INSERT INTO users.utenti_esterni (
                cf, nome, cognome, data_nascita, comune_residenza,
                cap, indirizzo, telefono1, mail, id1, id2, numero_gg
            ) VALUES (
                $1, UPPER($2), UPPER($3), $4, $5,
                $6, $7, $8, $9, NULLIF($10,'')::int, NULLIF($11,'')::int, $12
            )
            ON CONFLICT (cf) DO UPDATE SET
                nome = EXCLUDED.nome,
                cognome = EXCLUDED.cognome,
                data_nascita = EXCLUDED.data_nascita,
                comune_residenza = EXCLUDED.comune_residenza,
                cap = EXCLUDED.cap,
                indirizzo = EXCLUDED.indirizzo,
                telefono1 = EXCLUDED.telefono1,
                mail = EXCLUDED.mail,
                id1 = EXCLUDED.id1,
                id2 = EXCLUDED.id2,
                numero_gg = EXCLUDED.numero_gg
            RETURNING (xmax = 0) AS inserted;
        ";

        $params_ue = [
            $cf, $nome, $cognome, $data_nascita, $comune,
            $cap, $indirizzo, $telefono, $mail, $id1, $id2, $numero_gg
        ];

        $res_ue = pg_query_params($conn, $query_ue, $params_ue);
        if ($res_ue === false) {
            $pgerr = pg_last_error($conn);
            logError("Errore upsert utenti_esterni riga #$line_no CF=$cf id1=$id1 id2=$id2: $pgerr");
            pg_query($conn, 'ROLLBACK');
            die("Errore nell'import (utenti_esterni). Vedi log.");
        }

        $row_ue = pg_fetch_assoc($res_ue);
        if ($row_ue && isset($row_ue['inserted']) && $row_ue['inserted'] === 't') {
            $imported++;
        } else {
            $updated++;
        }

        // UPSERT utenti_sistema (per ogni riga valida)
        $id_profilo = 8;
        $query_us = "
            INSERT INTO users.utenti_sistema (matricola_cf, id_profilo)
            VALUES ($1, $2)
            ON CONFLICT (matricola_cf) DO UPDATE SET
                id_profilo = EXCLUDED.id_profilo;
        ";
        $res_us = pg_query_params($conn, $query_us, [$cf, $id_profilo]);
        if ($res_us === false) {
            $pgerr = pg_last_error($conn);
            logError("Errore upsert utenti_sistema riga #$line_no CF=$cf: $pgerr");
            pg_query($conn, 'ROLLBACK');
            die("Errore nell'import (utenti_sistema). Vedi log.");
        }
    }

    fclose($handle);

    // DELETE allineamento per unità
    $deleted_total = 0;
    foreach ($cf_by_unit as $unit_key => $cf_list) {
        list($unit_id1, $unit_id2) = explode('|', $unit_key);

        if (!empty($cf_list)) {
            $placeholders = [];
            $params = [$unit_id1, $unit_id2];
            foreach ($cf_list as $i => $cf_val) {
                $placeholders[] = '$' . ($i + 3);
                $params[] = $cf_val;
            }
            $delete_query = "
                DELETE FROM users.utenti_esterni
                WHERE id1 = $1 AND id2 = $2
                AND cf NOT IN (" . implode(',', $placeholders) . ");
            ";
        } else {
            $delete_query = "
                DELETE FROM users.utenti_esterni
                WHERE id1 = $1 AND id2 = $2;
            ";
            $params = [$unit_id1, $unit_id2];
        }

        $res_del = pg_query_params($conn, $delete_query, $params);
        if ($res_del === false) {
            $pgerr = pg_last_error($conn);
            logError("Errore DELETE per unità id1=$unit_id1 id2=$unit_id2: $pgerr");
            pg_query($conn, 'ROLLBACK');
            die("Errore nell'import (delete per unità). Vedi log.");
        }
        $deleted_total += pg_affected_rows($res_del);
    }

    pg_query($conn, 'COMMIT');

    // Messaggio finale con contatori estesi
    $_SESSION['import_message'] =
        "Importazione completata: $records_read letti, $imported aggiunti, $updated aggiornati, $deleted_total eliminati, " .
        "$skipped_invalid_cf CF non validi, $skipped_bad_row righe malformate.";

    header("Location: ../add_volontario.php");
    exit;

} catch (Throwable $e) {
    @pg_query($conn, 'ROLLBACK');
    logError("Eccezione non gestita: " . $e->getMessage());
    die("Errore inatteso durante l'import. Vedi log.");
}
