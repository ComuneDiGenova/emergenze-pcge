<?php
require(explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php');
// Imposta l'intestazione della risposta per JSON
header('Content-Type: application/json');

// Recupera il codice della provincia passato come parametro GET
$provincia = $_GET['provincia'] ?? '';

if ($provincia) {
    // Query per ottenere i comuni della provincia selezionata
    $query = 'SELECT "Denominazione in italiano" AS nome
                FROM varie.comuni_italia
                WHERE (
                    CASE 
                        WHEN "Codice Città Metropolitana" = \'-\' OR "Codice Città Metropolitana" IS NULL
                            THEN "Codice Provincia"
                        ELSE "Codice Città Metropolitana"
                    END
                ) = $1
                ORDER BY "Denominazione in italiano"
                ';
    $result = pg_query_params($conn, $query, [$provincia]);

    // Controlla se la query ha avuto successo
    if ($result) {
        $comuni = [];
        while ($row = pg_fetch_assoc($result)) {
            $comuni[] = $row;
        }

        // Restituisci i dati in formato JSON
        echo json_encode($comuni);
    } else {
        // Gestione degli errori nella query
        echo json_encode(['error' => 'Errore nella query al database']);
    }
} else {
    // Caso in cui non viene fornita alcuna provincia
    echo json_encode(['error' => 'Parametro provincia mancante']);
}

// Chiudi la connessione al database
pg_close($conn);
?>
