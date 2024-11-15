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
	$query = "INSERT INTO geodb.lettura_mire (num_id_mira, id_lettura, data_ora) VALUES ($id, $id_lettura, '$data_ora')";
    $result = pg_query($conn, $query);

	echo $data_inizio;
	echo "<br>";

	// $query="INSERT INTO geodb.lettura_mire (num_id_mira,id_lettura,data_ora) VALUES(".$id.",".$_POST["tipo"].",'".$data_inizio."');"; 
	// echo $query;
	// $result = pg_query($conn, $query);
	// echo "<br>";

	// $query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('geodb','".$_SESSION["Utente"] ."', 'Inserita lettura mira . ".$id."');";
	// $result = pg_query($conn, $query_log);

	// echo "<br>";
	// echo $query_log;

	// header("location: ../mire.php");

?>
