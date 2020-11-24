<?php

session_start();
require('../validate_input.php');

echo $_SESSION['user']. "<br>";

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';



$descrizione= str_replace("'", "''", $_POST["desc"]);
$mail=$_POST["mail"];



echo "Descrizione:".$descrizione. "<br>";
echo "Mail:".$mail. "<br>";

//exit;





$query= "INSERT INTO users.t_mail_meteo(descrizione, mail";
//values
$query=$query.") VALUES ('".$descrizione."', '".$mail."'";
$query=$query.");";

echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION['user']."', 'Aggiunta mail per il monitoraggio meteo (".$mail.")');";
$result = pg_query($conn, $query_log);






//exit;
header("location: ../lista_mail_meteo.php");


?>
