<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$uo=$_GET["cod"];
$mail=$_GET["mail"];
$idt=$_POST["idt"];


echo "<br>";

$query="UPDATE users.t_mail_incarichi SET id_telegram='".$idt."' WHERE cod='".$uo."' AND mail='".$mail."';";

echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', 'Aggiunto Telegram ID alla mail ".$mail." dall'Unit� Operativa ".$uo."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../edit_mail_uo.php?id=".$uo);


?>