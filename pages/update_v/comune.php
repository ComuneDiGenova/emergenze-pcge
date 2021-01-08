<?php

session_start();
//require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=pg_escape_string($_GET["id"]);

$comune=pg_escape_string($_POST["comune"]);


$query="UPDATE users.utenti_esterni SET comune_residenza=$1 WHERE cf=$2";
//echo $query;
//exit;
//$result = pg_query($conn, $query);
$result = pg_prepare($conn, "myquery", $query);
$result = pg_execute($conn, "myquery", array($comune,$cf));


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update comune residenza volontario  CF: ".$cf."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../update_volontario.php?id=".$cf);


?>