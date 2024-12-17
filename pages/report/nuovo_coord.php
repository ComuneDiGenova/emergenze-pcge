<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

//$id=$_GET["id"];
//$id=str_replace("'", "", $id);
echo print_r($_POST)
exit;
$cf=$_POST["cf"];
$inizio_data=$_POST["inizio_data"].' '.$_POST["start_hh"].':'.$_POST["start_mm"];
$fine_data=$_POST["fine_data"].' '.$_POST["end_hh"].':'.$_POST["end_mm"];
//$d1 = new DateTime($inizio_data);
//$d2 = new DateTime($fine_data);
$d1 =  strtotime($inizio_data);
$d2 =  strtotime($fine_data);


if ($d1 >= $d2) {
	echo 'La data/ora di fine ('.$fine_data.') deve essere posteriore alla data/ora di inizio ('.$inizio_data.'). ';
	echo '<br><a href="../attivita_sala_emergenze.php"> Torna alla pagina precedente';
	exit;
}

require('check_turni.php');

//$d1 = DateTime::createFromFormat('Y-m-d H:M', strtotime($inizio_data));
//$d2 = DateTime::createFromFormat('Y-m-d H:M', $fine_data);
echo $inizio_data;
echo "<br>";
echo $fine_data;
echo "<br>";
echo $d1;
echo "<br>";
echo $d2;
echo "<br>";
if ($d1 >= $d2) {
	echo "Errore: la data di inizio (".$inizio_data.") deve essere antecedente la fine (".$fine_data.")";
	exit;
}
//exit;

$query="INSERT INTO report.t_coordinamento (matricola_cf,data_start,data_end, warning_turno) VALUES";
$query= $query." ('".$cf."','".$inizio_data."','".$fine_data."','".$wt."');";
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('report','".$operatore ."', 'Inserimento coordinatore sala. CF: ".$cf."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
//header("location: ../reportistica.php");
header('Location: ' . $_SERVER['HTTP_REFERER']);



?>