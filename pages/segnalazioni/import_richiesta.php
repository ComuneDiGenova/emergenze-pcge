<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
//require('../check_evento.php');


//$id=$_GET["id"];

$nextPage = ($_POST['ric']=='richiesta') ? 'import_richiesta.php' : 'import_segnalazione.php';

$id=str_replace("'", "", $id);

$uo_inserimento = $_POST["uo_ins"];

$descrizione= str_replace("'", "''", $_POST["descrizione_richiesta"]);
$nome= str_replace("'", "''", $_POST["nome"]);
//$cognome= str_replace("'", "''", $_POST["cognome"]);
$altro= str_replace("'", "''", $_POST["altro"]);
$note_segnalante= str_replace("'", "''", $_POST["note_segnalante"]);






//echo "La gestione della segnalazione e' attualmente in fase di sviluppo. Ci scusiamo per il disagio<br>";
#segnalante

$query_max= "SELECT max(id) FROM segnalazioni.t_segnalanti;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_segnalante=$r_max["max"]+1;
	} else {
		$id_segnalante=1;	
	}
}

echo $id_segnalante;
echo "<br>";


$query= "INSERT INTO segnalazioni.t_segnalanti( id, id_tipo_segnalante, nome_cognome";
if ($altro!=''){
	$query= $query .", altro_tipo";
}
if ($_POST["telefono"]!=''){
	$query= $query .", telefono";
}
if ($note_segnalante!=''){
	$query= $query .", note";
}
//values
$query=$query.") VALUES (".$id_segnalante.", ".$_POST["tipo_segn"].", '".$nome."' ";
if ($altro!=''){
	$query= $query .", '".$altro."'";
}
if ($_POST["telefono"]!=''){
	$query= $query .", '".$_POST["telefono"]."'";
}
if ($note_segnalante!=''){
	$query= $query .", '".$note_segnalante."'";
}

$query=$query.");";

echo $query;
$result=pg_query($conn, $query);

echo "<br>";
//exit;

//**************************************************************
// INSERIMENTO SEGNALAZIONI


$query_max= "SELECT max(id) FROM segnalazioni.t_richieste_nverde;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_richiesta=$r_max["max"]+1;
	} else {
		$id_richiesta=1;	
	}
}

echo $id_richiesta;
echo "<br>";




$query="INSERT INTO segnalazioni.t_richieste_nverde(id, uo_ins, id_segnalante, descrizione, id_evento, id_operatore ";
$query=$query.") VALUES ("; 
$query=$query." ".$id_richiesta.", '".$uo_inserimento."', ".$id_segnalante.",'".$descrizione."',".$_POST["evento"].",'".$_SESSION["operatore"]."' ";
$query=$query.");";

echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";







$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$_SESSION["operatore"] ."', 'Inserita richiesta nverde ".$id_richiesta."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
//header("location: ../dettagli_segnalazione.php?id=".$id_segnalazione);
header("location: ../elenco_richieste.php");

?>