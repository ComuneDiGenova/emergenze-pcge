<?php
session_start();
require('../validate_input.php');
require(explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php');

// Sanificazione parametro id evento
$id_evento = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Header JSON per Bootstrap Table
header('Content-Type: application/json; charset=utf-8');

// QUERY:
// Usa la stessa sorgente dati di griglia_segnalazioni.php / griglia_segnalazioni_eventi_attivi.php
// Qui assumo una vista tipo "segnalazioni.v_elenco_segnalazioni"
// con campi: id, criticita, localizzazione, in_lavorazione, note, id_evento
// Adatta il nome vista/tabella se necessario.
$query = "
    SELECT
        row_number() OVER (ORDER BY id) AS num,
        criticita AS tipologia,
        localizzazione,
        CASE
            WHEN in_lavorazione IS TRUE THEN 'In lavorazione'
            WHEN in_lavorazione IS FALSE THEN 'Chiusa'
            WHEN in_lavorazione IS NULL THEN 'Chiusa'
            ELSE ''
        END AS stato,
        note
    FROM segnalazioni.v_segnalazioni_lista
    WHERE id_evento = $1
    ORDER BY id;
";

$result = pg_query_params($conn, $query, array($id_evento));

$rows = [];
if ($result) {
    while ($r = pg_fetch_assoc($result)) {
        $rows[] = $r;
    }
}

echo json_encode($rows);