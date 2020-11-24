<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

$id_lavorazione=$_GEt['lav'];

$id=$_GET["id"];



echo "Id".$id."<br>";

echo "Id_lav".$id_segnalazione_lav."<br>";


//exit;





$query_oggetto="UPDATE segnalazioni.join_oggetto_rischio SET
attivo='f', aggiornamento=now() WHERE id_segnalazione=".$id." and attivo='t';";
$result_oggetto = pg_query($conn, $query_oggetto);
echo $query_oggetto;
echo "<br>";	


//exit;


$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
$query=$query."".$id_segnalazione_lav.",'Rimosso oggetto precedentemente definito a rischio dalla segnalazione ".$id."');";
echo $query;

//exit;
$result = pg_query($conn, $query);
echo "<br>";



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Rimosso oggetto a rischio a segnalazione ".$id."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_segnalazione.php?id=".$id);


?>