<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

$id_segnalazione=$_POST["id"];



$query_max= "SELECT max(id) FROM segnalazioni.t_segnalazioni_in_lavorazione;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_lavorazione=$r_max["max"]+1;
	} else {
		$id_lavorazione=1;	
	}
}

echo $id_lavorazione;
echo "<br>";


$id_profilo=$_POST["uo"];
//**************************************************************
// CERCO TIPO PROFILO


$query_g= "SELECT descrizione FROM users.profili_utilizzatore WHERE id=".$id_profilo.";";
$result_g = pg_query($conn, $query_g);
while($r_g = pg_fetch_assoc($result_g)) {
	$profilo=$r_g["descrizione"];
}




//**************************************************************
// CERCO LA GEOMETRIA DA SEGNALAZIONI


$query_g= "SELECT geom FROM segnalazioni.t_segnalazioni WHERE id=".$id_segnalazione.";";
$result_g = pg_query($conn, $query_g);
while($r_g = pg_fetch_assoc($result_g)) {
	$geom=$r_g["geom"];
}

echo $id_segnalazione;
echo "<br>";




$query="INSERT INTO segnalazioni.t_segnalazioni_in_lavorazione(id, id_profilo, geom ";
$query=$query.") VALUES ("; 
$query=$query." ".$id_lavorazione.", ".$id_profilo.",'".$geom."');";
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";

if(isset($_POST["mun"])) {
	if ($_POST["mun"]=='on'){
		$query="INSERT INTO segnalazioni.join_segnalazioni_in_lavorazione 
		(id_segnalazione_in_lavorazione, id_segnalazione, sospeso) 
		VALUES (".$id_lavorazione.",".$id_segnalazione.", 't');";
		echo $query;
	} else {
		$query="INSERT INTO segnalazioni.join_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, id_segnalazione) VALUES (".$id_lavorazione.",".$id_segnalazione.");";
		echo $query;
	}
} else {
	$query="INSERT INTO segnalazioni.join_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, id_segnalazione) VALUES (".$id_lavorazione.",".$id_segnalazione.");";
	echo $query;
}
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
if ($_POST["mun"]=='on' and $id_profilo==3){
	$query=$query."".$id_lavorazione.",'La segnalazione n. ".$id_segnalazione." è stata inviata dal municipio al profilo ".$profilo."');";
} else {
	$query=$query."".$id_lavorazione.",'La segnalazione n. ".$id_segnalazione." è stata presa in carico come profilo ".$profilo."');";
}
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


if(isset($_POST["mun"])) {
	if ($_POST["mun"]=='on' and $id_profilo==3){
		$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$CF ."', 'Invio segnalazione ".$id_segnalazione." alla PC');";
	} else {
		$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$CF ."', 'Presa in carico segnalazione ".$id_segnalazione."');";
	} 
}
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_segnalazione.php?id=".$id_segnalazione);


?>