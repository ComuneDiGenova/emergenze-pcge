<?php
	session_start();
	require('../validate_input.php');

	include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
	
	$id = str_replace("'", "", $_GET["id"]);
	$id_lettura = $_POST["tipo"];
	$hh_start = $_POST["hh_start"];
	$mm_start = $_POST["mm_start"];
	$utente = $_SESSION["Utente"];

	// Crea la data/ora combinando la data corrente con ora e minuti dal form
	$data_ora = date('Y-m-d') . ' ' . $hh_start . ':' . $mm_start;

	// Inserisci la lettura a DB
	$query = "INSERT INTO geodb.lettura_mire (num_id_mira, id_lettura, data_ora, data_ora_reg) VALUES ($id, $id_lettura, '$data_ora', now() AT TIME ZONE 'Europe/Rome')";
    $result = pg_query($conn, $query);
	
	// Ricarico la pagina
	header("location: ../mire.php");
	exit;
?>
