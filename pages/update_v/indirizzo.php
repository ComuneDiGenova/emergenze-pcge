<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=pg_escape_string($_GET["id"]);
echo $cf . "<br>";
//exit;
$indirizzo=pg_escape_string($_POST["indirizzo"]);
$cap=pg_escape_string($_POST["cap"]);
echo $cap."<br>";

/*$query="UPDATE users.utenti_esterni SET indirizzo='".$indirizzo."'";
if ($_POST["cap"]){
	$query= $query. " , cap='".$cap."' ";
}
$query= $query. " where cf=$cf;";

echo $query;
exit;
$result = pg_query($conn, $query);
*/

$query="UPDATE users.utenti_esterni SET indirizzo=$1, cap=$2 WHERE cf like $3";
$result = pg_prepare($conn, "myquery", $query);
if ($_POST["cap"]){
	echo "cap not null";
	$result = pg_execute($conn, "myquery", array($indirizzo,$cap,$cf));
} else {
	echo "cap null";
	$result = pg_execute($conn, "myquery", array($indirizzo,NULL,$cf));
}
echo pg_result_error($result);
//exit;


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update indirizzo residenza volontario  CF: ".$cf."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../update_volontario.php?id=".$cf);


?>