<?php
	session_start();
	require('../validate_input.php');

	include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

	$id=$_GET["id"];
	$id=str_replace("'", "", $id);

	if ($_POST["data_inizio"]==''){
		date_default_timezone_set('Europe/Rome');
		$data_inizio = date('Y-m-d H:i');
	} else{
		$data_inizio=$_POST["data_inizio"].' '.$_POST["hh_start"].':'.$_POST["mm_start"];
	}

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