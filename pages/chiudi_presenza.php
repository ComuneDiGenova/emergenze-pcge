<?php
session_start();
//require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
$id_pres=pg_escape_string($_GET['id']);
//echo $id_pres;
/* $profilo=(int)pg_escape_string($_GET['p']);
$livello=pg_escape_string($_GET['l']);
if ($profilo==3){
	$filter = ' ';
} else if($profilo==8){
	$filter= ' WHERE id_profilo=\''.$profilo.'\' and nome_munic = \''.$livello.'\' ';
} else {
	$filter= ' WHERE id_profilo=\''.$profilo.'\' ';
}
 */

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="UPDATE users.t_presenze set operativo = 'f', data_fine = now() where id = $1;";
    $result = pg_prepare($conn, "myquery0", $query);
	$result = pg_execute($conn, "myquery0", array($id_pres));
    header ("Location: elenco_presenti.php");

	pg_close($conn);
	#echo $rows ;
}

?>


