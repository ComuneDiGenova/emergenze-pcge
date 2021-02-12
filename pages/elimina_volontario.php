<?php

session_start();
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=pg_escape_string($_GET["id"]);

//echo $cf;
//exit;

$query="INSERT INTO users.utenti_esterni_eliminati SELECT * FROM users.utenti_esterni where cf =$1;";
//echo $query;
//exit;
$result = pg_prepare($conn,"myquery", $query);
$result = pg_execute($conn,"myquery", array($cf));

$query="DELETE FROM users.utenti_esterni WHERE cf=$1;";
//echo $query;
//exit;
$result = pg_prepare($conn,"myquery1", $query);
$result = pg_execute($conn,"myquery1", array($cf));


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users',$1, 'Eliminato volontario  CF: $2');";
$result = pg_query($conn, $query_log);
$result = pg_prepare($conn,"myquery2", $query_log);
$result = pg_execute($conn,"myquery2", array($_SESSION["Utente"], $cf));


//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ./lista_volontari.php");


?>