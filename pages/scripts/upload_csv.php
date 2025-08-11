<?php
session_start();


// Connessione al database
$conn_path = explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
if (!file_exists($conn_path)) {
    die("Errore: file di connessione non trovato.");
}
include $conn_path;

if (!isset($conn) || !$conn) {
    logError("Connessione al database non valida. pg_last_error: " . (function_exists('pg_last_error') ? pg_last_error() : 'n/a'));
    die("Errore di connessione al database.");
}

$logFile = __DIR__.'/logs/upload_csv_error_log.txt';


function logError($message) {
    global $logFile;
    $timestamp = date("Y-m-d H:i:s");
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// Controlla che un file sia stato caricato
if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
    $csvFile = $_FILES['csvFile']['tmp_name'];
    $fileName = $_FILES['csvFile']['name'];
    $fileType = $_FILES['csvFile']['type'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    // Controllo del formato: deve essere .csv
    $allowedMimeTypes = ['text/csv', 'text/plain', 'application/vnd.ms-excel'];
    if (!in_array($fileType, $allowedMimeTypes) || strtolower($fileExtension) !== 'csv') {
        die("Errore: il file caricato non è un CSV valido. Per favore carica un file con estensione .csv.");
    }

    // Apri il file CSV
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        // Leggi la prima riga come intestazione
        $headers = fgetcsv($handle, 1000, ";");

        // Rimuovi il BOM dal primo elemento dell'array delle intestazioni, se presente
        if (isset($headers[0])) {
            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', $headers[0]);
        }

        // Verifica che le colonne richieste siano presenti
        $required_columns = ['CF', 'nome', 'cognome', 'data_nascita', 'comune', 'telefono', 'mail'];
        foreach ($required_columns as $column) {
            if (!in_array($column, $headers)) {
                echo "Colonna richiesta mancante: $column.";
                exit;
            }
        }

        // Importa i record (setta null in caso di campi vuoti)
        // function nullable($value) {
        //     return empty($value) ? "NULL" : "'" . pg_escape_string(trim($value)) . "'";
        // }

        $imported = 0;
        $updated = 0;
        $cf_csv_list = [];
        $firstRow = true;
        $cf_by_unit = []; // chiave: "id1|id2" => array di CFs

        pg_query($conn, 'BEGIN');

        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            // Salta le righe vuote o errate
            if (empty($data) || count($data) !== count($headers)) {
                continue;
            }
            
            // Associa i dati alle colonne
            $row = array_combine($headers, $data);

            // Sanifica i dati obbligatori
            $cf = strtoupper(trim($row['CF']));
            $cf_csv_list[] = $cf;
            $nome = strtoupper(pg_escape_string(trim($row['nome'])));
            $cognome = strtoupper(pg_escape_string(trim($row['cognome'])));
            $data_nascita = pg_escape_string(trim($row['data_nascita']));
            $comune = pg_escape_string(trim($row['comune']));

            // Sanifica i dati opzionali
            $cap = trim($row['cap'] ?? '') === '' ? null : trim($row['cap']);
            $indirizzo = trim($row['indirizzo'] ?? '') === '' ? null : trim($row['indirizzo']);
            $telefono = trim($row['telefono'] ?? '') === '' ? null : trim($row['telefono']);
            $mail = trim($row['mail'] ?? '') === '' ? null : trim($row['mail']);
            $id1 = trim($row['unita_operativa_I_liv'] ?? '') === '' ? null : trim($row['unita_operativa_I_liv']);
            $id2 = trim($row['unita_operativa_II_liv'] ?? '') === '' ? null : trim($row['unita_operativa_II_liv']);
            $numero_gg = trim($row['Numero_tessera'] ?? '') === '' ? null : trim($row['Numero_tessera']);

            if ($id1 !== null && $id2 !== null) {
                $unit_key = "{$id1}|{$id2}";
                $cf_by_unit[$unit_key][] = $cf;
            }

            // Costruisce la query INSERT per la tabella utenti_esterni, in caso di conflitto esegue UPDATE
            $query_ue = "
                INSERT INTO users.utenti_esterni (
                    cf, nome, cognome, data_nascita, comune_residenza,
                    cap, indirizzo, telefono1, mail, id1, id2, numero_gg
                ) VALUES (
                    $1, $2, $3, $4, $5,
                    $6, $7, $8, $9, $10, $11, $12
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
                $cf,
                strtoupper($nome),
                strtoupper($cognome),
                $data_nascita,
                $comune,
                $cap,
                $indirizzo,
                $telefono,
                $mail,
                $id1,
                $id2,
                $numero_gg
            ];
            $res_ue = pg_query_params($conn, $query_ue, $params_ue);
            if ($res_ue === false) {
                logError("Errore upsert utenti_esterni CF=$cf: " . pg_last_error($conn));
            } else {
                $row_ue = pg_fetch_assoc($res_ue);
                if ($row_ue && isset($row_ue['inserted']) && $row_ue['inserted'] === 't') {
                    $imported++;
                } else {
                    $updated++;
                }
            }
        }

            // Costruisce la query INSERT per la tabella utenti_sistema, settando id profilo = 8 (solo visualizzazione)
            // in caso di conflitto esegue UPDATE
            $id_profilo = 8;
            $query_us = "
                INSERT INTO users.utenti_sistema (matricola_cf, id_profilo)
                VALUES ($1, $2)
                ON CONFLICT (matricola_cf) DO UPDATE SET
                    id_profilo = EXCLUDED.id_profilo;
            ";
            $res_us = pg_query_params($conn, $query_us, [$cf, $id_profilo]);
            if ($res_us === false) {
                logError("Errore upsert utenti_sistema CF=$cf: " . pg_last_error($conn));
            }

        fclose($handle);

        // === Eliminazione utenti non più presenti nel CSV per la stessa id1-id2 ===
        $deleted_total = 0;
        foreach ($cf_by_unit as $unit_key => $cf_list) {
            [$unit_id1, $unit_id2] = explode('|', $unit_key);

                        $placeholders = [];
            $params = [];
            foreach ($cf_list as $i => $cf_val) {
                $placeholders[] = '$' . ($i + 3);
                $params[] = $cf_val;
            }

            $delete_query = "
                DELETE FROM users.utenti_esterni
                WHERE id1 = $1 AND id2 = $2
                AND cf NOT IN (" . implode(',', $placeholders) . ");
            ";
            $delete_params = array_merge([$unit_id1, $unit_id2], $params);
            $res_del = pg_query_params($conn, $delete_query, $delete_params);
            if ($res_del === false) {
                logError("Errore DELETE per unità id1=$unit_id1 id2=$unit_id2: " . pg_last_error($conn));
                continue;
            }
            $deleted = pg_affected_rows($res_del);
            $deleted_total += $deleted;
        }

        // Salva il messaggio nella variabile di sessione
        $_SESSION['import_message'] = "Importazione completata: $imported aggiunti, $updated aggiornati, $deleted_total eliminati.";

        // Reindirizza alla pagina iniziale
        pg_query($conn, 'COMMIT');
        header("Location: ../add_volontario.php");
        exit;
    } else {
        echo "Errore durante l'apertura del file.";
    }
} else {
    echo "Errore nel caricamento del file. Riprova.";
}
?>
