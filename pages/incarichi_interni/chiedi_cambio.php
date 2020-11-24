<?php


session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id_incarico= $_GET['id'];
$id_lavorazione= $_GET['l'];




$query="INSERT INTO segnalazioni.t_incarichi_interni_richiesta_cambi (id_incarico)
 VALUES (".$id_incarico."); ";

echo $query."<br>";
//exit;
$result=pg_query($conn, $query);




$query= "INSERT INTO segnalazioni.t_comunicazioni_incarichi_interni(id_incarico, testo";

$query= $query .")VALUES (".$id_incarico.", 'Richiesto cambio squadra'";

$query= $query .");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";






if ($id_lavorazione!=''){
	$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";
	
	//values
	$query=$query.") VALUES (".$id_lavorazione.", 'Chiesto cambio squadra per l'incarico interno ".$id_incarico." </i><br>- <a class=\"btn btn-info\" href=\"dettagli_incarico_interno.php?id=".$id."\"> Visualizza dettagli </a>'";
	
	$query=$query.");";
	
	
	echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
}

$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('incarichi_interni','".$operatore ."', 'Chiesto cambio squadra per incarico interno ".$id_incarico."');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


//exit;


header("location: ../dettagli_incarico_interno.php?id=".$id_incarico);




?>