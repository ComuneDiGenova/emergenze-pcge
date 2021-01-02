<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user']. "<br>";

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id=pg_escape_string($_GET['id']);



$query= "UPDATE users.t_mail_meteo SET valido = 't' ";
$query=$query."where id=".$id.";";

echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION['user']."', 'Mail ".$id." per il monitoraggio meteo attivata');";
$result = pg_query($conn, $query_log);






//exit;
header("location: ../lista_mail_meteo.php");


?>
