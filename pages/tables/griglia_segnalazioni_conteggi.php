<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';

$id = $_GET["id"];

if (!$conn) {
    die('Connessione fallita !<br />');
} else {
    $query = "SELECT s.criticita, count(s.id) as pervenute, r.risolte
            FROM segnalazioni.v_segnalazioni_all s
            LEFT JOIN segnalazioni.v_count_risolte r ON r.criticita = s.criticita and r.id_evento = $1
            WHERE s.id_evento = $1
            GROUP BY s.criticita, r.risolte;";

    $result = pg_query_params($conn, $query, [$id]);

    $rows = array();
    while ($row = pg_fetch_assoc($result)) {
        $rows[] = $row;
    }
    

    // Stampa i dati corretti
    if (!empty($rows)) {
        print json_encode($rows);
    } else {
        echo "[{\"NOTE\": \"No data\"}]";
    }

    pg_close($conn);
}
?>