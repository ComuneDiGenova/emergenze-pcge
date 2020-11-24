<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

$now=date("Y-m-d H:i:s");
echo $now .'<br>';


$table=$_GET["t"];
//$id=str_replace("'", "", $id);

echo $table .'<br>';


$data_start1=$_GET["s1"];
//$id=str_replace("'", "", $id);

echo $data_start1 .'<br>';

$data_end1=$_GET["e1"];
//$id=str_replace("'", "", $id);

echo $data_end1 .'<br>';



$cf=$_POST["cf"];

echo $cf .'<br>';


$data_start=$_POST["data_start"];

echo $data_start .'<br>';


$data_end=$_POST["data_end"];

echo $data_end .'<br>';


//$d1 = DateTime::createFromFormat('Y-m-d H:M', strtotime($data_inizio));
//$d2 = DateTime::createFromFormat('Y-m-d H:M', $data_fine);

echo "<br>";

echo "<br>";




$query="UPDATE report.".$table." set data_start = '".$data_start."', data_end='".$data_end."',
		modificato ='t', modifica = concat(modifica,'Modificato da operatore ".$operatore ." il ".$now."<br>')
		WHERE matricola_cf='".$cf."' 
		and data_start='".$data_start1."' 
		and data_end='".$data_end1."';";

echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) 
VALUES ('report','".$operatore ."', 'Update turno - tabella =".$table.", matricola_cf=".$cf."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../storico_sala_emergenze.php");
//header('Location: ' . $_SERVER['HTTP_REFERER']);


?>