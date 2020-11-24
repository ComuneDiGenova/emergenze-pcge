<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=$_GET["id"];




$query="UPDATE users.utenti_esterni SET indirizzo='".$_POST["indirizzo"]."'";
if ($_POST["cap"]){
	$query= $query. " , cap='".$_POST["cap"]."' ";
}
$query= $query. " where cf=$cf;";

echo $query;
//exit;

$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update indirizzo residenza volontario  CF: ".$_POST['CF']."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../update_volontario.php?id=".$cf);


?>