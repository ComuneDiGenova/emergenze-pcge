<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET['s'];
$mail=$_GET['m'];

$query="DELETE FROM users.t_mail_squadre WHERE cod='".$id."' AND mail=".$mail.";";
echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', 'Squadra con id: ".$id_squadra." messa a disposizione');";
$result = pg_query($conn, $query_log);

//exit;
header("location: ../edit_squadra.php?id=".$id);
?>