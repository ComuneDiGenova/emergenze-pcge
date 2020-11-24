<?php


session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id_incarico= $_GET['id'];
$id_squadra_old= $_GET['os'];



echo "<br>Attualmente in lavorazione.. ci scusiamo per il disagio.<br>";
//exit;


$query="INSERT INTO segnalazioni.t_incarichi_interni_richiesta_cambi (id_incarico,eseguito)
 VALUES (".$id_incarico.",'t'); ";

echo $query."<br>";
//exit;
$result=pg_query($conn, $query);


/*$query="UPDATE segnalazioni.t_incarichi_interni_richiesta_cambi SET eseguito='t' WHERE id_incarico=".$id_incarico." and eseguito is null; ";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
*/

$query="UPDATE segnalazioni.join_incarichi_interni_squadra SET valido='false' WHERE id_incarico=".$id_incarico." and id_squadra=".$id_squadra_old."; ";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);



$query="UPDATE users.t_squadre SET id_stato=2 WHERE id=".$id_squadra_old.";";
echo $query;
//exit;
$result=pg_query($conn, $query);





//exit;


$query= "INSERT INTO segnalazioni.t_comunicazioni_incarichi_interni(id_incarico, testo";

$query= $query .")VALUES (".$id_incarico.", 'Cambio squadra effettuato'" ;

$query= $query .");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";






if ($id_lavorazione!=''){
	$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";
	
	//values
	$query=$query.") VALUES (".$id_lavorazione.", 'La squadra ".$squadra_old." sta abbandonando l'incarico interno ".$id_incarico." (sostituzione in corso) </i><br>- <a class=\"btn btn-info\" href=\"dettagli_incarico_interno.php?id=".$id."\"> Visualizza dettagli </a>'";
	
	$query=$query.");";
	
	
	echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
}

$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('incarichi interni','".$operatore ."', 'Cambio squadra per presidio (o incarico interno) ".$id_incarico." accordato e in corso');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


//exit;




header("location: ../dettagli_incarico_interno.php?id=".$id_incarico);




?>