<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');


$id=$_GET["id"];
$id=str_replace("'", "", $id);

$data_inizio=$_POST["data_inizio"].' '.$_POST["hh_start"].':'.$_POST["mm_start"];
$data_fine=$_POST["data_fine"].' '.$_POST["hh_end"].':'.$_POST["mm_end"];
//$d1 = new DateTime($data_inizio);
//$d2 = new DateTime($data_fine);
$d1 =  strtotime($data_inizio);
$d2 =  strtotime($data_fine);


if ($d1 > $d2) {
	echo 'La data di fine allerta deve essere posteriore alla data di inizio allerta. ';
	echo '<br><a href="../dettagli_evento.php"> Torna alla pagina precedente';
	exit;
}
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
if ($d1 > $d2) {
	echo "Errore: la data di inizio allerta (".$data_inizio.") deve essere antecedente la fine dell'allerta stessa(".$data_fine.")";
	exit;
}


$query="INSERT INTO eventi.t_attivazione_nverde (id_evento,data_ora_inizio,
data_ora_fine) VALUES(".$id.",'".$data_inizio."','".$data_fine."');"; 
echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('eventi','".$operatore ."', 'Creazione attivazione nverde per evento n. ".$id."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_evento.php");


?>