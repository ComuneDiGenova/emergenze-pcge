<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


echo "<h2> La gestione dei Provvedimento Cautelari e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2>";


//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //provvedimento_cautelare










$query= "UPDATE segnalazioni.t_provvedimenti_cautelari SET rimosso='t' 
WHERE id=".$id.";";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";




$query= "INSERT INTO segnalazioni.t_ora_rimozione_provvedimenti_cautelari(
            id_provvedimento)
    VALUES (".$id.");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";




$query= "INSERT INTO segnalazioni.t_comunicazioni_provvedimenti_cautelari(
            id_provvedimento, testo)
    VALUES (".$id.", 'Rimosso provvedimento cautelare');";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";




$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";

//values
$query=$query.") VALUES (".$id_lavorazione.", ' Provvedimento cautelare".$id." rimosso - <a class=\"btn btn-info\" href=\"dettagli_provvedimento_cautelare.php?id=".$id."\"> Visualizza dettagli </a>'";

$query=$query.");";


echo $query."<br>";
//exit;

$result=pg_query($conn, $query);
echo "Result:". $result."<br>";




$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('Povvedimenti cautelari','".$operatore ."', 'Provvedimento cautelare ".$id." preso in carico');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


echo "Result:". $result."<br>";



//exit;
header("location: ../dettagli_provvedimento_cautelare.php?id=".$id);


?>