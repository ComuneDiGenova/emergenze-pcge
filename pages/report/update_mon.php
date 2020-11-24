<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

$id=$_GET["id"];
//$id=str_replace("'", "", $id);


$aggiornamento= str_replace("'", "''", $_POST["aggiornamento"]);




//$d1 = DateTime::createFromFormat('Y-m-d H:M', strtotime($data_inizio));
//$d2 = DateTime::createFromFormat('Y-m-d H:M', $data_fine);

echo "<br>";

echo "<br>";



$query="UPDATE report.t_aggiornamento_meteo set aggiornamento =  ";
$query= $query." '".$aggiornamento."', data_aggiornamento=now() ";
$query= $query." WHERE id =".$id.";";

echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('report','".$operatore ."', 'Update aggiornamento meteo id=".$id."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
//header("location: ../reportistica.php");
header('Location: ' . $_SERVER['HTTP_REFERER']);


?>