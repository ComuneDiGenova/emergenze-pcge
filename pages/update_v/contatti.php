<?php

session_start();
//require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=pg_escape_string($_GET["id"]);

$telefono1=pg_escape_string($_POST["telefono1"]);
echo "<br>".$telefono1;
$query="UPDATE users.utenti_esterni SET telefono1=$1 WHERE cf=$2;";
$result = pg_prepare($conn, "myquery1", $query);
$result = pg_execute($conn, "myquery1", array($telefono1,$cf));

$mail=pg_escape_string($_POST["mail"]);
$query="UPDATE users.utenti_esterni SET mail=$1 WHERE cf=$2;";
$result = pg_prepare($conn, "myquery2", $query);
$result = pg_execute($conn, "myquery2", array($mail,$cf));


$telefono2=pg_escape_string($_POST["telefono2"]);
echo "<br>".$telefono2;
$query="UPDATE users.utenti_esterni SET telefono2=$1 WHERE cf=$2;";
$result = pg_prepare($conn, "myquery3", $query);
if ($_POST["telefono2"]){
	$result = pg_execute($conn, "myquery3", array($telefono2,$cf));
} else {
	$result = pg_execute($conn, "myquery3", array(NULL,$cf));
}


$fax=pg_escape_string($_POST["fax"]);
echo "<br>".$fax;
$query="UPDATE users.utenti_esterni SET fax=$1 WHERE cf=$2;";
$result = pg_prepare($conn, "myquery4", $query);
if ($_POST["fax"]){
	$result = pg_execute($conn, "myquery4", array($fax,$cf));
} else {
	$result = pg_execute($conn, "myquery4", array(NULL,$cf));
}

$telegram_id=pg_escape_string($_POST["telegram_id"]);
echo "<br>".$telegram_id;
$query="UPDATE users.utenti_sistema SET telegram_id=$1, telegram_attivo=$2 WHERE matricola_cf=$3;";
$result = pg_prepare($conn, "myquery5", $query);
if ($_POST["telegram_id"]){
	$result = pg_execute($conn, "myquery5", array($telegram_id,'t',$cf));
} else {
	$result = pg_execute($conn, "myquery5", array(NULL,'f',$cf));
}

/* $telegram_id=pg_escape_string($_POST["telegram_id"]);
echo "<br>".$telegram_id;
$query="UPDATE users.utenti_esterni SET telegram_id=$1, telegram_attivo=$2 WHERE cf=$3;";
$result = pg_prepare($conn, "myquery5", $query);
if ($_POST["telegram_id"]){
	$result = pg_execute($conn, "myquery5", array($telegram_id,'t',$cf));
} else {
	$result = pg_execute($conn, "myquery5", array(NULL,'f',$cf));
} */

/*$query="UPDATE users.utenti_esterni SET telefono1='".$_POST["telefono1"]."', mail='".$_POST["mail"]."' ";
if ($_POST["telefono2"]){
	$query= $query. " , telefono2='".$_POST["telefono2"]."' ";
}
if ($_POST["fax"]){
	$query= $query. " , fax='".$_POST["fax"]."' ";
}
$query= $query. " where cf=$cf;";

echo $query;*/
//exit;

//$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update contatti volontario  CF: ".$_POST['CF']."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../update_volontario.php?id=".$cf);


?>