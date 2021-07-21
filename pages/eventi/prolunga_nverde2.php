<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

$id_evento=$_GET["e"];
//$id_foc=$_GET["a"];
$time=$_GET["t"];


$data_fine=$_POST["data_fine"].' '.$_POST["hh_end"].':'.$_POST["mm_end"];
echo $data_fine;

echo "<br>";

$query="UPDATE eventi.t_attivazione_nverde SET data_ora_fine='".$data_fine."' where id_evento=".$id_evento." AND data_ora_inizio='".$time."';";
echo $query;
//exit;
$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('eventi','".$operatore ."', 'Prolunga orari nverde associati a evento".$id_evento."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_evento.php?e=".$id_evento."");


?>