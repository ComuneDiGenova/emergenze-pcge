<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

//$id=$_GET["id"];
//$id=str_replace("'", "", $id);

$cf=pg_escape_string($_POST["cf"]);
echo $cf."<br>";
$data_inizio=$_POST["data_inizio"].' '.$_POST["hh_start"].':'.$_POST["mm_start"];
$data_fine=$_POST["data_fine"].' '.$_POST["hh_end"].':'.$_POST["mm_end"];
//$d1 = new DateTime($data_inizio);
//$d2 = new DateTime($data_fine);
$d1 =  strtotime($data_inizio);
$d2 =  strtotime($data_fine);


if ($d1 >= $d2) {
	echo 'La data/ora di fine ('.$data_fine.') deve essere posteriore alla data/ora di inizio ('.$data_inizio.'). ';
	echo '<br><a href="../attivita_sala_emergenze.php"> Torna alla pagina precedente';
	exit;
}

require('check_turni.php');

//$d1 = DateTime::createFromFormat('Y-m-d H:M', strtotime($data_inizio));
//$d2 = DateTime::createFromFormat('Y-m-d H:M', $data_fine);
echo $data_inizio;
echo "<br>";
echo $data_fine;
echo "<br>";
echo $d1;
echo "<br>";
echo $d2;
echo "<br>";
if ($d1 >= $d2) {
	echo "Errore: la data di inizio (".$data_inizio.") deve essere antecedente la fine (".$data_fine.")";
	exit;
}
//exit;

$query="INSERT INTO report.t_operatore_nverde (matricola_cf,data_start,data_end, warning_turno) VALUES";
//$query= $query." ('".$cf."','".$data_inizio."','".$data_fine."','".$wt."');";
$query= $query." ($1, $2, $3, $4);";

echo $query;
//exit;
$result = pg_prepare($conn, myquery, $query);
$result = pg_execute($conn, myquery, array($cf,$data_inizio,$data_fine,$wt));
//$result = pg_query($conn, $query);
echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('report','".$operatore ."', 'Inserimento operatore Numero Verde: ".$cf."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
//header("location: ../reportistica.php");
header('Location: ' . $_SERVER['HTTP_REFERER']);



?>