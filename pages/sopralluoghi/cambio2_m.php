<?php


session_start();
require('../validate_input.php');

//echo $_SESSION['operatore'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id_sopralluogo= $_GET['id'];
$id_squadra_old= $_GET['os'];



echo "<br>Attualmente in lavorazione.. ci scusiamo per il disagio.<br>";
//exit;


$query="UPDATE segnalazioni.t_sopralluoghi_mobili_richiesta_cambi SET eseguito='t' WHERE id_sopralluogo=".$id_sopralluogo." and eseguito is null; ";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);


$query="UPDATE segnalazioni.join_sopralluoghi_mobili_squadra SET valido='false' WHERE id_sopralluogo=".$id_sopralluogo." and id_squadra=".$id_squadra_old."; ";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);



$query="UPDATE users.t_squadre SET id_stato=2 WHERE id=".$id_squadra_old.";";
echo $query;
//exit;
$result=pg_query($conn, $query);





//exit;


$query= "INSERT INTO segnalazioni.t_comunicazioni_sopralluoghi_mpbili(id_sopralluogo, testo";

$query= $query .")VALUES (".$id_sopralluogo.", 'Cambio squadra effettuato'" ;

$query= $query .");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";






if ($id_lavorazione!=''){
	$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";
	
	//values
	$query=$query.") VALUES (".$id_lavorazione.", 'La squadra ".$squadra_old." sta abbandonando il presidio ".$id_sopralluogo." (sostituzione in corso) </i><br>- <a class=\"btn btn-info\" href=\"dettagli_sopralluogo.php?id=".$id."\"> Visualizza dettagli </a>'";
	
	$query=$query.");";
	
	
	echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
}

$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('sopralluoghi','".$_SESSION['operatore']."', 'Cambio squadra per presidio (o sopralluogo) ".$id_sopralluogo." accordato e in corso');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


//exit;




header("location: ../dettagli_sopralluogo_mobile.php?id=".$id_sopralluogo);




?>