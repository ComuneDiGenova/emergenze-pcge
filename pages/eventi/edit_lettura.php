<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET["id"];
$id=str_replace("'", "", $id);

$data_inizio=$_POST["data"];
echo $data_inizio;
echo "<br>";

$query="SELECT id_lettura FROM geodb.lettura_mire  WHERE num_id_mira=".$id." AND data_ora='".$data_inizio."';"; 
echo $query;
echo "<br>";
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$old_id_lettura=$r['id_lettura'];
}

$query="INSERT INTO geodb.lettura_mire_modifiche (num_id_mira, data_ora, old_id_lettura) VALUES (".$id." , '".$data_inizio."',". $old_id_lettura.");"; 
echo $query;
echo "<br>";
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query="UPDATE geodb.lettura_mire SET id_lettura = ".$_POST["tipo"]." WHERE num_id_mira=".$id." AND data_ora='".$data_inizio."';"; 
// volendo potrei modificare la data di registrazione, ma in questo modo sono più evidenti eventuali modifiche
//$query="UPDATE geodb.lettura_mire SET id_lettura = ".$_POST["tipo"].", data_ora_reg = now() WHERE num_id_mira=".$id." AND data_ora='".$data_inizio."';"; 
echo $query;
echo "<br>";
//exit;
$result = pg_query($conn, $query);
echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('geodb','".$_SESSION["Utente"] ."', 'Modificata lettura mira . ".$id." delle ore ".$data_inizio."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../mira.php?id=".$id."");


?>