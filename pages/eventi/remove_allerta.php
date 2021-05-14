<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id_evento=$_GET["e"];
$id_allerta=$_GET["a"];
$time=$_GET["t"];

//echo $time. "<br>";

$id=str_replace("'", "", $id);

$query="DELETE FROM eventi.join_tipo_allerta WHERE id_evento=".$id_evento." AND  id_tipo_allerta=".$id_allerta." AND data_ora_inizio_allerta='".$time."';";
echo $query;
//exit;
$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Rimuovi allerta associata a evento".$id_evento."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_evento.php?e=".$id_evento."");


?>