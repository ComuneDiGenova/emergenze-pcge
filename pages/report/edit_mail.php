<?php

session_start();
require('../validate_input.php');

echo $_SESSION['user']. "<br>";

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id=$_GET['id'];
$descrizione= str_replace("'", "''", $_POST["desc"]);
$mail=$_POST["mail"];



echo "Descrizione:".$descrizione. "<br>";
echo "Mail:".$mail. "<br>";

//exit;





$query= "UPDATE users.t_mail_meteo SET descrizione = ";
//values
$query=$query." '".$descrizione."', mail= '".$mail."'";
$query=$query."where id=".$id.";";

echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION['user']."', 'Modificata mail ".$id." per il monitoraggio meteo (".$mail.")');";
$result = pg_query($conn, $query_log);






//exit;
header("location: ../lista_mail_meteo.php");


?>
