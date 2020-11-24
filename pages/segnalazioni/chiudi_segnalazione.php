<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

$id_segnalazione_lav=$_GET["id_lav"];
$id=$_GET["id"];
$note= str_replace("'", "''", $_POST["note"]);



$check_error=0;

if($_POST["invio"]=='man') {
	$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
	$query=$query."".$id_segnalazione_lav.",'Segnalazione risolta per la PC, ma problema ancora attivo e inviata al sistema \"Manutenzioni\"');";
	//echo $query;
	$result = pg_query($conn, $query);
	$id_civico=$_POST["idcivico"];
	$geom=$_POST["geom"];
	$descrizione="Tipo criticità SW emergenze: ".$_POST["crit"] . " - Descrizione segnalazione: ".$_POST["descr"] . " - Descrizione chiusura: " .$note;
	$descrizione=str_replace('"','',$descrizione);
	if($id_civico !='') {
		$queryc= "SELECT * FROM geodb.civici WHERE id=".$id_civico.";";
		//echo $queryc;
		$resultc=pg_query($conn, $queryc);
		while($rc = pg_fetch_assoc($resultc)) {
			$codvia= $rc['codvia'];
			$ncivico=$rc['numero'];
			$colore='';
			$colore=$rc['colore'];
			$lettera='';
			$lettera=$rc['lettera'];
		}
	} else {
		$queryc= "SELECT *, st_distance(st_transform(geom,4326),'".$geom."') as distance  
		FROM geodb.civici ORDER BY distance LIMIT 1;";
		//echo $queryc;
		echo "<br>";
		$resultc=pg_query($conn, $queryc);
		while($rc = pg_fetch_assoc($resultc)) {
			$codvia= $rc['codvia'];
			$ncivico=$rc['numero'];
			$colore='';
			$colore=$rc['colore'];
			$lettera='';
			$lettera=$rc['lettera'];
		}
	}
	$command_options= ' -v '.$codvia.' -n '.$ncivico.' -i '.$id.' ';
	if ($lettera!=''){
		$command_options=$command_options. ' -l ' .$lettera .' ';
	}
	if ($colore!=''){
		$command_options=$command_options. ' -c ' .$colore .' ';
	}
	$command_options=$command_options. ' -d "' .$descrizione .'" ';
	//comando definitivo
	$command = escapeshellcmd('/usr/bin/python3 emergenze2manutenzioni.py '.$command_options.' ');
	
	echo '<br>';
	echo $command;
	echo '<br>';
	$output = shell_exec($command);
	echo $output[1];
	if ($output==200){
		echo '<h1><i class="fas fa-check"></i> La segnalazione è stata correttamente trasmessa al sistema di manutenzioni con id ';
		$check_error=0;
		$id_man=substr($output,4);
		echo $id_man."</h1>";
	} else {
		echo "ERRORE: problema nella trasmissione della segnalazione al sistema delle manutenzioni.";
		echo "<br>Si prega di contattare via mail l'amministratore di sistema specificando la problematica.";
		echo "<br>Id segnalazione: ".$id." <br>";
		echo "<br>Command options: ".$command_options." <br>";
		//exit;
		$check_error=1;
	}
	echo "<br>";
}


$query= "UPDATE segnalazioni.t_segnalazioni_in_lavorazione SET in_lavorazione='f'";
$query=$query.", descrizione_chiusura='".$note."' ";
if($_POST["invio"]=='man') {
	$query=$query.", invio_manutenzioni='t' ";
	$query=$query.", id_man=".$id_man." ";
	
}
$query=$query." WHERE id=".$id_segnalazione_lav.";";
//echo $query;
$result = pg_query($conn, $query);
echo "<br>";


if($_POST["invio"]=='llpp') {
	$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
	$query=$query."".$id_segnalazione_lav.",'Segnalazione risolta per la PC, ma problema ancora attivo e predispost flag per l'invio a \"LLPP\"');";
	//echo $query;
	$result = pg_query($conn, $query);
	echo "<br>";
}


$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
$query=$query."".$id_segnalazione_lav.",'Chiusura delle segnalazioni. (id_lavorazione= ".$id_segnalazione_lav.")');";
//echo $query;

//exit;
$result = pg_query($conn, $query);
echo "<br>";



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'La segnalazione in lavorazione con ".$id_segnalazione_lav." è stata chiusa');";
$result = pg_query($conn, $query_log);
echo "<br>";
//echo $query_log;

//exit;
if($check_error==1) {
	echo 'Clicca <a href="../dettagli_segnalazione.php?id='.$id.'">qua</a> per tornare alla pagina della segnalazione';
	exit;
}
?>
<script>
	//alert('Segnalazione in chiusura');
</script>
<?php
//header("location: ../dettagli_segnalazione.php?id=".$id);

echo '<div style="text-align: center;"><img src="../../img/Elipsis.gif" width="25%" alt=""></div>';
//echo '<br>L\'incarico &egrave stato correttamente assegnato, ma si &egrave riscontrato un problema nell\'invio della mail.';
echo '<br><h1><i class="fas fa-times"></i> Segnalazione chiusa. Entro 5" verrai re-indirizzato alla prima pagina</h1>';

header("refresh:5;url=../index.php");
?>