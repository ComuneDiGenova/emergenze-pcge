<?php
session_start();

// Connessione al database
$conn_path = explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
if (!file_exists($conn_path)) {
    die("Errore: file di connessione non trovato.");
}
include $conn_path;

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
        function nullable($value) {
            return empty($value) ? "NULL" : "'" . pg_escape_string(trim($value)) . "'";
        }

        $imported = 0;
        $updated = 0;
        $firstRow = true;
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            // Salta le righe vuote o errate
            if (empty($data) || count($data) !== count($headers)) {
                continue;
            }
            
            // Associa i dati alle colonne
            $row = array_combine($headers, $data);

            // Sanifica i dati obbligatori
            $cf = strtoupper(trim($row['CF']));
            $nome = strtoupper(pg_escape_string(trim($row['nome'])));
            $cognome = strtoupper(pg_escape_string(trim($row['cognome'])));
            $data_nascita = pg_escape_string(trim($row['data_nascita']));
            $comune = pg_escape_string(trim($row['comune']));

            // Sanifica i dati opzionali
            $cap = nullable($row['cap']);
            $indirizzo = nullable($row['indirizzo']);
            $telefono = nullable($row['telefono']);
            $mail = nullable($row['mail']);
            $id1 = nullable($row['unita_operativa_I_liv']);
            $numero_gg = nullable($row['Numero_tessera']);

            // Costruisce la query INSERT per la tabella utenti_esterni, in caso di conflitto esegue UPDATE
            $query_ue = "INSERT INTO users.utenti_esterni (cf, nome, cognome, data_nascita, comune_residenza, cap, indirizzo, telefono1, mail, id1, numero_gg)
                    VALUES ('$cf', '$nome', '$cognome', '$data_nascita', '$comune', $cap, $indirizzo, $telefono, $mail, $id1, $numero_gg)
                    ON CONFLICT (cf) DO NOTHING
                    RETURNING cf;";


            $result_insert = pg_query($conn, $query_ue);

            if ($result_insert && pg_num_rows($result_insert) > 0) {
                $imported++;
            } else {
                $query_update = "UPDATE users.utenti_esterni SET
                        nome = '$nome',
                        cognome = '$cognome',
                        data_nascita = '$data_nascita',
                        comune_residenza = '$comune',
                        cap = $cap,
                        indirizzo = $indirizzo,
                        telefono1 = $telefono,
                        mail = $mail,
                        id1 = $id1,
                        numero_gg = $numero_gg
                    WHERE cf = '$cf';
                ";
                $result_update = pg_query($conn, $query_update);
                if ($result_update) {
                    $updated++;
                }
            }


            // Costruisce la query INSERT per la tabella utenti_sistema, settando id profilo = 8 (solo visualizzazione)
            // in caso di conflitto esegue UPDATE
            $id_profilo = 8;
            $query_us = "INSERT INTO users.utenti_sistema (matricola_cf, id_profilo)
                    VALUES ('$cf', $id_profilo)
                    ON CONFLICT (matricola_cf) 
                    DO UPDATE SET 
                        id_profilo = {$id_profilo};";

            $result = pg_query($conn, $query_us);
        }

        fclose($handle);

        // Salva il messaggio nella variabile di sessione
        $_SESSION['import_message'] = "Importazione completata: $imported record aggiunti, $updated record aggiornati.";

        // Reindirizza alla pagina iniziale
        header("Location: ../add_volontario.php");
        exit;
    } else {
        echo "Errore durante l'apertura del file.";
    }
} else {
    echo "Errore nel caricamento del file. Riprova.";
}
?>
