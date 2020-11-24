<?php

session_start();
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=$_GET["id"];


$query="INSERT INTO users.utenti_esterni_eliminiati SELECT * FROM users.utenti_esterni where cf =".$cf.";";
echo $query;
//exit;
$result = pg_query($conn, $query);


$query="DELETE FROM users.utenti_esterni WHERE cf=$cf;";
echo $query;
//exit;
$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Eliminato volontario  CF: ".$_POST['CF']."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ./lista_volontari.php");


?>