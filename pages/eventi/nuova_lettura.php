<?php

session_start();

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET["id"];
$id=str_replace("'", "", $id);

$data_inizio=$_POST["data_inizio"].' '.$_POST["hh_start"].':'.$_POST["mm_start"];
//$d1 = new DateTime($data_inizio);
//$d2 = new DateTime($data_fine);
$d1 =  strtotime($data_inizio);


echo $data_inizio;
echo "<br>";

echo $d1;
echo "<br>";



$query="INSERT INTO geodb.lettura_mire (num_id_mira,id_lettura,data_ora) VALUES(".$id.",".$_POST["tipo"].",'".$data_inizio."');"; 
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('geodb','".$_SESSION["Utente"] ."', 'Inserita lettura mira . ".$id."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../mire.php");


?>