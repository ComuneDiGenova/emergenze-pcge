<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$uo=$_GET["uo"];
$mail=$_POST["mail"];
$matricola_cf=$_GET["cf"];

echo $matricola_cf;

echo "<br>";

$query="INSERT INTO users.t_mail_squadre(cod, mail ";
if ($matricola_cf!=''){
	$query=$query.", matricola_cf ";
}

$query=$query.") VALUES (";
$query=$query." '".$uo."', '".$mail."' ";
if ($matricola_cf!=''){
	$query=$query.",'" .$matricola_cf. "'";
}
$query=$query.");";
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', 'Aggiunta mail ".$mail." per la squadra ".$uo."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../edit_squadra.php?id=".$uo);


?>