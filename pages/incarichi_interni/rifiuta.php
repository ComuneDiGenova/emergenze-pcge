<?php

session_start();

//echo $_SESSION['user'];

include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';


echo "<h2> La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2>";


//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //incarico

$id_lavorazione=$_POST["id_lavorazione"];

$note_rifiuto= str_replace("'", "''", $_POST["note_rifiuto"]);
$uo=$_POST["uo"];
$squadra=$_POST["squadra"];


echo "Incarico:".$id. "<br>";


//exit;


$query= "UPDATE segnalazioni.t_incarichi_interni SET note_rifiuto='".$note_rifiuto."' 
WHERE id=".$id.";";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);



$query= "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico";

//values
$query=$query.") VALUES (".$id.", 4 ";

$query=$query.");";

echo $query."<br>";
//exit;
$result=pg_query($conn, $query);




$query="UPDATE users.t_squadre SET id_stato=2 WHERE id=".$squadra.";";
echo $query;
//exit;
$result=pg_query($conn, $query);



$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";

//values
$query=$query.") VALUES (".$id_lavorazione.", ' Incarico ".$id." rifiutato dalla seguente squadra: ".$uo." - <a class=\"btn btn-info\" href=\"dettagli_incarico_interno.php?id=".$id."\"> Visualizza dettagli </a>'";

$query=$query.");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Incarico interno ".$id." rifiutato');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


//exit;
header("location: ../dettagli_incarico_interno.php?id=".$id);


?>
