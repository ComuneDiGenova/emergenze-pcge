<?php


session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id_sopralluogo= $_GET['id'];
$id_lavorazione= $_GET['l'];




$query="INSERT INTO segnalazioni.t_sopralluoghi_mobili_richiesta_cambi (id_sopralluogo)
 VALUES (".$id_sopralluogo."); ";

echo $query."<br>";
//exit;
$result=pg_query($conn, $query);




$query= "INSERT INTO segnalazioni.t_comunicazioni_sopralluoghi_mobili(id_sopralluogo, testo";

$query= $query .")VALUES (".$id_sopralluogo.", 'Richiesto cambio squadra'";

$query= $query .");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";





/*
if ($id_lavorazione!=''){
	$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";
	
	//values
	$query=$query.") VALUES (".$id_lavorazione.", 'Chiesto cambio squadra per il presidio ".$id_sopralluogo." </i><br>- <a class=\"btn btn-info\" href=\"dettagli_sopralluogo.php?id=".$id."\"> Visualizza dettagli </a>'";
	
	$query=$query.");";
	
	
	echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
}
*/

$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('sopralluoghi','".$operatore ."', 'Chiesto cambio squadra per presidio (o sopralluogo) ".$id_sopralluogo."');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


//exit;


header("location: ../dettagli_sopralluogo_mobile.php?id=".$id_sopralluogo);




?>