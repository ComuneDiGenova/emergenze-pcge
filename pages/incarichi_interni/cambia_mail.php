<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
//require('../check_evento.php');


$uo=$_GET["s"];
$mail=$_POST["mailsq"];
$matricola_cf=$_GET["cf"];

echo $matricola_cf;

echo "<br>";

if ($mail!=''){
	$query="UPDATE users.t_mail_squadre SET mail = '".$mail."' WHERE cod='".$uo."'
	 AND matricola_cf='".$matricola_cf."' ;";
} else {
	$query="DELETE FROM users.t_mail_squadre WHERE cod='".$uo."'
	 AND matricola_cf='".$matricola_cf."' ;";
}
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["operatore"] ."', 'Modificata mail ".$mail." per la squadra ".$uo."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../edit_squadra.php?id=".$uo);


?>