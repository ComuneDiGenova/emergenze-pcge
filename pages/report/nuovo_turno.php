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

// Recupero e sanificazione parametri
$cf = htmlspecialchars($_POST['cf'], ENT_QUOTES, 'UTF-8');
$data_inizio = $_POST['data_inizio'] . ' ' . $_POST['hh_start'] . ':' . $_POST['mm_start'];
$data_fine = $_POST['data_fine'] . ' ' . $_POST['hh_end'] . ':' . $_POST['mm_end'];
$title = htmlspecialchars($_POST['title'], ENT_QUOTES, 'UTF-8');
$db_table = htmlspecialchars($_POST['db_table'], ENT_QUOTES, 'UTF-8');

// Validazioni
if (strtotime($data_inizio) >= strtotime($data_fine)) {
    die('La data di fine deve essere successiva a quella di inizio.');
}

// echo "CF: $cf, Data Inizio: $data_inizio, Data Fine: $data_fine<br>";
// exit;

// Controllo sovrapposizione turni
$wt = checkTurniSovrapposti($conn, $cf, $data_inizio, $data_fine) ? 't' : 'f';

// Inserimento turno
$query = "INSERT INTO $db_table (matricola_cf, data_start, data_end, warning_turno) 
          VALUES ($1, $2, $3, $4);";

$result = pg_query_params($conn, $query, [$cf, $data_inizio, $data_fine, $wt]);

if (!$result) {
    die("Errore durante l'inserimento in $db_table.");
}

// Query di log dell'operazione
$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) 
              VALUES ('report', $1, $2);";

$result_log = pg_query_params($conn, $query_log, [$operatore, "Inserimento $title. CF: $cf"]);

if (!$result_log) {
    die("Errore durante l'inserimento nel log.");
}

// Reindirizzamento alla pagina precedente
if ($result) {
    header('Location: ../attivita_sala_emergenze.php?status=success&message=Turno%20aggiunto%20con%20successo.');
} else {
    header('Location: ../attivita_sala_emergenze.php?status=error&message=Errore%20durante%20l%27inserimento.');
}
exit;

?>
