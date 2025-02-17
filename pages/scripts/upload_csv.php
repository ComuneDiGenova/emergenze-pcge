<?php
// Inizio della sessione
session_start();

// Connessione al database
$conn_path = explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
if (!file_exists($conn_path)) {
    die("Errore: file di connessione non trovato.");
}
include $conn_path;

// Controlla che un file sia stato caricato
if (isset($_FILES['csvFile']) && $_FILES['csvFile']['error'] === UPLOAD_ERR_OK) {
    $csvFile = $_FILES['csvFile']['tmp_name'];
    
    // Apri il file CSV
    if (($handle = fopen($csvFile, 'r')) !== FALSE) {
        // Leggi la prima riga come intestazione
        $headers = fgetcsv($handle, 1000, ",");

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
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {

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

            // Costruisce la query INSERT
            $query = "INSERT INTO users.utenti_esterni (cf, nome, cognome, data_nascita, comune_residenza, cap, indirizzo, telefono1, mail, id1, numero_gg)
                    VALUES ('$cf', '$nome', '$cognome', '$data_nascita', '$comune', $cap, $indirizzo, $telefono, $mail, $id1, $numero_gg)
                    ON CONFLICT (cf) DO NOTHING;"; // Ignora duplicati basati sul CF

            $result = pg_query($conn, $query);
            if ($result) {
                $imported++;
            }
        }

        fclose($handle);

        // Salva il messaggio nella variabile di sessione
        $_SESSION['import_message'] = "Importazione completata: $imported record inseriti con successo.";

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
