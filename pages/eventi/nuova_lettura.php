<?php
	session_start();
	require('../validate_input.php');

	include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

	$id = str_replace("'", "", $_GET["id"]);
	$tipo = $_POST["tipo"];
	$hh_start = $_POST["hh_start"];
	$mm_start = $_POST["mm_start"];
	$utente = $_SESSION["Utente"]

	// Crea la data/ora combinando la data corrente con ora e minuti dal form
	$data_inizio = date('Y-m-d') . ' ' . $hh_start . ':' . $mm_start;

	// Inserisci la lettura a DB
	$query = "INSERT INTO geodb.lettura_mire (num_id_mira, id_lettura, data_ora) VALUES ($id, $tipo, $data_inizio)";
    $result = pg_query($conn, $query);

	if (!$result) {
        throw new Exception("Errore nell'inserimento della lettura.");
    }
	
	// Inserisci il log dell'operazione
	$log_message = "Inserita lettura mira . " . $id;
	$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('geodb', $utente, $log_message)";
    $result_log = pg_query($conn, $query_log);

	if (!$result_log) {
        throw new Exception("Errore nell'inserimento del log.");
    }

	// Ricarico la pagina
	header("location: ../mire.php");
?>
