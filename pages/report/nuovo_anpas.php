<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

// echo print_r($_POST);
$cf=$_POST["cf"];
$inizio_data=$_POST["inizio_data"].' '.$_POST["inizio_hh"].':'.$_POST["inizio_mm"];
$fine_data=$_POST["fine_data"].' '.$_POST["fine_hh"].':'.$_POST["fine_mm"];
$d1 =  strtotime($inizio_data);
$d2 =  strtotime($fine_data);

if ($d1 >= $d2) {
	echo 'La data/ora di fine ('.$fine_data.') deve essere posteriore alla data/ora di inizio ('.$inizio_data.'). ';
	echo '<br><a href="../attivita_sala_emergenze.php"> Torna alla pagina precedente';
	exit;
}

require('check_turni.php');

echo $inizio_data;
echo "<br>";
echo $fine_data;
echo "<br>";
echo $d1;
echo "<br>";
echo $d2;
echo "<br>";
echo $wt;
// if ($d1 >= $d2) {
// 	echo "Errore: la data di inizio (".$inizio_data.") deve essere antecedente la fine (".$fine_data.")";
// 	exit;
// }


// $query="INSERT INTO report.t_operatore_anpas (matricola_cf,data_start,data_end, warning_turno) VALUES";
// $query= $query." ('".$cf."','".$inizio_data."','".$fine_data."','".$wt."');";
// // echo $query;

// $result = pg_query($conn, $query);
// // echo "<br>";

// $query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('report','".$operatore ."', 'Inserimento operatore presidio sanitario: ".$cf."');";
// $result = pg_query($conn, $query_log);

// // echo "<br>";
// // echo $query_log;

// header('Location: ' . $_SERVER['HTTP_REFERER']);

?>
