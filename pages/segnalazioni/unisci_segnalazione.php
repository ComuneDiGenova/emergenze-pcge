<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id_segnalazione_unire=$_GET["id_from"];

$id_segnalazione_lav=$_GET["id_to"];


$query_id= "SELECT id_segnalazione_in_lavorazione FROM segnalazioni.join_segnalazioni_in_lavorazione WHERE id_segnalazione=".$id_segnalazione_lav.";";
$result_id = pg_query($conn, $query_id);
while($r_id = pg_fetch_assoc($result_id)) {
		$id_lavorazione=$r_id["id_segnalazione_in_lavorazione"];
}

echo $id_lavorazione;
echo "<br>";


$query="INSERT INTO segnalazioni.join_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, id_segnalazione) VALUES (".$id_lavorazione.",".$id_segnalazione_unire.");";
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
$query=$query."".$id_lavorazione.",'Unione con la segnalazione n. ".$id_segnalazione_unire."');";
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'La segnalazione ".$id_segnalazione_unire." è stata unita alla segnalazione $id_segnalazione_lav');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_segnalazione.php?id=".$id_segnalazione_unire);


?>