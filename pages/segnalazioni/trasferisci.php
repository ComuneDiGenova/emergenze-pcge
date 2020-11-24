<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id_segnalazione_lav=$_GET["l"];
$profilo=$_GET["t"];
$id=$_GET["id"];
echo $profilo;



$query= "UPDATE segnalazioni.t_segnalazioni_in_lavorazione SET id_profilo=".$profilo.";";
$result = pg_query($conn, $query);
echo "<br>";


$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
$query=$query."".$id_segnalazione_lav.",'La segnalazione è stata trasferita alla centrale');";
echo $query;

//exit;
$result = pg_query($conn, $query);
echo "<br>";



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'La segnalazione in lavorazione con ".$id_segnalazione_lav." è stata trasferita di pertinenza');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_segnalazione.php?id=".$id);


?>