<?php
    session_start();
    require('../validate_input.php');

    include explode('emergenze-pcge', getcwd())[0].'emergenze-pcge/conn.php';

    // Verifica se la richiesta è POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Assicurati che il parametro 'data' sia presente
        if (!isset($_POST['data'])) {
            echo "Dati mancanti.";
            exit;
        }

        // Decodifica i dati JSON inviati
        $data = json_decode($_POST['data'], true);

        // Verifica che la decodifica del JSON sia andata a buon fine
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Errore nella decodifica del JSON: " . json_last_error_msg();
            exit;
        }

        // Verifica che l'array non sia vuoto
        if (empty($data)) {
            echo "Nessun ID fornito!";
            exit;
        }

        // Inizializza una lista per i valori da inserire
        $values = [];

        $data_inizio = date('Y-m-d H:i'); 

        
        // Costruisci la lista di valori per la query
        foreach ($data as $item) {
            // Assicurati che ogni elemento abbia sia 'id' che 'value'
            if (isset($item['id']) && isset($item['value'])) {
                $id = pg_escape_string($item['id']); // Prevenzione SQL Injection
                $value = pg_escape_string($item['value']);
                $values[] = "($id, $value, '$data_inizio')";
            } else {
                echo "Formato dati non valido";
                exit;
            }
        }

        // Costruisci la query di inserimento
        if (!empty($values)) {
            $values_str = implode(", ", $values);
            $query = "INSERT INTO geodb.lettura_mire (num_id_mira, id_lettura, data_ora) VALUES $values_str;";
            
            // Esegui la query di inserimento
            $result = pg_query($conn, $query);
            if (!$result) {
                echo "Errore durante l'inserimento delle letture: " . pg_last_error($conn);
                exit;
            }

            // Log dell'operazione
            $mira_ids_str = implode(",", array_column($data, 'id'));
            $query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('geodb', '" . pg_escape_string($_SESSION["Utente"]) . "', 'Inserite letture per le mire: $mira_ids_str');";
            
            $result_log = pg_query($conn, $query_log);
            if (!$result_log) {
                echo "Errore durante il log dell'operazione: " . pg_last_error($conn);
            } else {
                echo "Inserimento completato con successo.";
            }
        } else {
            echo "Nessun valore da inserire.";
        }
    } else {
        echo "Richiesta non valida.";
    }

    header("location: ../mire.php")
?>
