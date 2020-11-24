<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$matr=str_replace("'", "", $_GET["matr"]);
$cf=str_replace("'", "", $_GET["cf"]);
$matr_cf=$matr;
$matr_cf=$matr_cf."".$cf;
echo "matricola_cf:".$matr_cf."<br>";


$query="UPDATE users.utenti_sistema SET valido='t' where matricola_cf='".$matr_cf."' ;";
echo $query;
$result = pg_query($conn, $query);
$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Sospesi i permessi di : ".$matr_cf."');";
$result = pg_query($conn, $query_log);








//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
if ($matr==''){
	header("location: ../lista_volontari.php");
} else {
	header("location: ../lista_dipendenti.php");
}
?>