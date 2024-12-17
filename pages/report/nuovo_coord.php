<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
require('../check_evento.php');
require('check_turni.php');

// Recupero della sessione
$operatore = $_SESSION['username'];

// echo print_r($_POST);
// exit;

// Recupero e sanificazione input
$cf = htmlspecialchars($_POST["cf"], ENT_QUOTES, 'UTF-8');
$data_inizio = htmlspecialchars($_POST["data_inizio"], ENT_QUOTES, 'UTF-8') . ' ' . $_POST["hh_start"] . ':' . $_POST["mm_start"];
$data_fine = htmlspecialchars($_POST["data_fine"], ENT_QUOTES, 'UTF-8') . ' ' . $_POST["hh_end"] . ':' . $_POST["mm_end"];

// Conversione delle date in timestamp
$d1 = strtotime($data_inizio);
$d2 = strtotime($data_fine);

// Validazione delle date
if ($d1 >= $d2) {
    echo 'La data/ora di fine (' . $data_fine . ') deve essere posteriore alla data/ora di inizio (' . $data_inizio . '). ';
    echo '<br><a href="../attivita_sala_emergenze.php">Torna alla pagina precedente</a>';
    exit;
}

// Controllo sovrapposizione turni
$wt = checkTurniSovrapposti($conn, $cf, $data_inizio, $data_fine) ? 't' : 'f';

// Query di inserimento del turno
$query = "INSERT INTO report.t_coordinamento (matricola_cf, data_start, data_end, warning_turno)
          VALUES ($1, $2, $3, $4);";

$result = pg_query_params($conn, $query, [$cf, $data_inizio, $data_fine, $wt]);

if (!$result) {
    echo "Errore durante l'inserimento del turno: " . pg_last_error($conn);
    exit;
}

// Query di log dell'operazione
$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) 
              VALUES ('report', $1, $2);";

$result_log = pg_query_params($conn, $query_log, [$operatore, "Inserimento coordinatore sala. CF: $cf"]);

if (!$result_log) {
    echo "Errore durante l'inserimento del log: " . pg_last_error($conn);
    exit;
}

// Reindirizzamento alla pagina precedente
header('Location: ' . $_SERVER['HTTP_REFERER']);
exit;
?>
