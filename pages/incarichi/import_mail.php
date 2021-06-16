<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$uo=$_GET["uo"];
$mail=$_POST["mail"];
$idt=$_POST["telegramid"];
$matricola_cf=$_POST["cf"];

echo $matricola_cf;

echo "<br>";

$query="INSERT INTO users.t_mail_incarichi(cod, mail ";
if ($matricola_cf!=''){
	$query=$query.", matricola_cf ";
}
if ($idt!=''){
	$query=$query.", id_telegram ";
}

$query=$query.") VALUES (";
$query=$query." '".$uo."', '".$mail."' ";
if ($matricola_cf!=''){
	$query=$query.",'" .$matricola_cf. "'";
}
if ($idt!=''){
	$query=$query.",'" .$idt. "'";
}
$query=$query.");";
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', 'Aggiunta mail ".$mail." per l'Unit� Operativa ".$uo."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../edit_mail_uo.php?id=".$uo);


?>