<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$matr=str_replace("'", "", $_POST["matr"]);
$cf=str_replace("'", "", $_POST["cf"]);

echo "cf:".$cf."<br>";
$matr_cf=$matr;
$matr_cf=$matr_cf."".$cf;
echo "matricola_cf:".$matr_cf."<br>";
$profilo=$_POST["profilo"];
echo "profilo:".$profilo."<br>";

$profilo_array=explode('_', $profilo);

$profilo=$profilo_array[0];
$municipio=$profilo_array[1];

echo "profilo:".$profilo."<br>";
echo "codice_municipio:".$municipio."<br>";
//exit;


// verifico se necessario update o insert
$check_update=0;
$query0= "SELECT * FROM users.utenti_sistema where matricola_cf='".$matr_cf."' ;";
$result0 = pg_query($conn, $query0);
while($r0 = pg_fetch_assoc($result0)) {
    $check_update=1;
}
 
echo "check_update:".$check_update."<br>";


if ($profilo=='no' and $check_update==1 ){
	$query="DELETE FROM users.utenti_sistema WHERE matricola_cf='".$matr_cf."' ;";
	echo $query;
	$result = pg_query($conn, $query);
	$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Rimossi permessi di : ".$matr_cf."');";
	$result = pg_query($conn, $query_log);


} else if ($profilo!='no' and $check_update==1){
	$query="UPDATE users.utenti_sistema SET id_profilo=".$profilo." ";
	if($municipio!='') {
		$query=$query. ", cod_municipio='".$municipio."' ";
	} else {
		$query=$query. ", cod_municipio=NULL ";
	}
	$query=$query. " WHERE matricola_cf='".$matr_cf."' ;";
	echo $query;
	$result = pg_query($conn, $query);
	$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update permessi di : ".$matr_cf."');";
	$result = pg_query($conn, $query_log);


} else if ($profilo!='no' and $check_update==0){
	$query="INSERT INTO users.utenti_sistema (matricola_cf, id_profilo";
	if($municipio!='') {
		$query=$query. ", cod_municipio";
	}
	$query=$query. ") VALUES (";
	$query=$query. "'".$matr_cf."', ". $profilo." ";
	if($municipio!='') {
		$query=$query. ", '".$municipio."' ";
	}
	$query=$query.");";
	echo $query;
	//exit;
	$result = pg_query($conn, $query);
	$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Aggiunta permessi utente : ".$matr_cf."');";
	$result = pg_query($conn, $query_log);
} else {
	echo "Non faccio niente <br>";
}





//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

echo "<br>Matr=".$matr;
exit;
if ($matr==''){
	//header("location: ../lista_volontari.php");
	header("location: ../update_volontario.php?id=".$cf);
} else {
	header("location: ../lista_dipendenti.php");
}
?>