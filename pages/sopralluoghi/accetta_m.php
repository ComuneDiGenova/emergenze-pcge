<?php

session_start();

//echo $_SESSION['user'];

include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';


echo "<h2> La gestione dei presidi mobili e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2>";


//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //sopralluogo


$id_lavorazione=$_POST["id_lavorazione"];
$data_inizio=$_POST["data_inizio"];
$hh=$_POST["hh_start"];
$mm=$_POST["mm_start"];

$time_preview= $data_inizio." ".$hh .":".$mm;
 
//$parziale=$_POST["parziale"];
$parziale='f';

$note= str_replace("'", "''", $_POST["note"]);
$uo=$_POST["uo"];

echo "sopralluogo:".$id. "<br>";
echo "Time preview:".$time_preview. "<br>";
echo "Note:".$note. "<br>";
echo "Parziale:".$parziale. "<br>";

//exit;


$query= "UPDATE segnalazioni.t_sopralluoghi_mobili SET time_preview='".$time_preview."' 
WHERE id=".$id.";";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";


$query= "INSERT INTO segnalazioni.stato_sopralluoghi_mobili(id_sopralluogo, id_stato_sopralluogo, parziale";

//values
$query=$query.") VALUES (".$id.", 2 , '".$parziale."'";

$query=$query.");";

echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";



if ($note!=''){

	$query= "INSERT INTO segnalazioni.t_comunicazioni_sopralluoghi_mobili(
	            id_sopralluogo, testo)
	    VALUES (".$id.", '".$note."');";
	
	
	echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
	echo "Result:". $result."<br>";
}


/*
$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";

//values
if ($parziale=='t'){
	$query=$query.") VALUES (".$id_lavorazione.", ' presidio mobile ".$id." preso in carico (parzialmente) dalla seguente squadra.: ".$uo." - <a class=\"btn btn-info\" href=\"dettagli_sopralluogo.php?id=".$id."\"> Visualizza dettagli </a>'";
} else {
	$query=$query.") VALUES (".$id_lavorazione.", ' presidio mobile ".$id." preso in carico dalla seguente squadra.: ".$uo." - <a class=\"btn btn-info\" href=\"dettagli_sopralluogo.php?id=".$id."\"> Visualizza dettagli </a>'";
}
$query=$query.");";


echo $query."<br>";*/
//exit;

$result=pg_query($conn, $query);
echo "Result:". $result."<br>";




$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'presidio mobile (o sopralluogo) ".$id." preso in carico');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


echo "Result:". $result."<br>";



//exit;
header("location: ../dettagli_sopralluogo_mobile.php?id=".$id);


?>