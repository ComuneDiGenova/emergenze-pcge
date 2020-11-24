<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');

// Filtro per tipologia di criticità
$getfiltri=$_GET["f"];
//echo $getfiltri;

//require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
//$filter=filtro($getfiltri);


$filtro_from=$_GET["from"];
$filtro_to=$_GET["to"];


//require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
//$filter=filtro($getfiltri);


if (strlen($filtro_from)>=12 || strlen($filtro_to)>=12){
		$check2=1;
	}
	
	if ($check2==1) {
		$filter = $filter . " AND (" ;
	}
	
	if (strlen($filtro_from)>=12 ) {
		$filter = $filter . " data_ora_invio > ".$filtro_from." ";
	}
	
	if (strlen($filtro_from)>=12 && strlen($filtro_to)>=12) {
		$filter = $filter . " AND " ;
	}
	
	if (strlen($filtro_to)>=12) {
		$filter = $filter . " data_ora_invio < ".$filtro_to." ";
	}
	
	if ($check2==1){
		$filter = $filter . ")" ;
	}



if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT id_evento, data_ora_invio, id_stato_provvedimenti_cautelari, descrizione_stato, 
	tipo_provvedimento, oggetto, descrizione, note_ente, id, id_segnalazione, rimosso 
	From segnalazioni.v_provvedimenti_cautelari_last_update where id > 1 ".$filter." ;";
    
   //echo $query ."<br>";
	$result = pg_query($conn, $query);
	//echo $query;
	#exit;
	$rows = array();
	while($r = pg_fetch_assoc($result)) {
    		$rows[] = $r;
    		//$rows[] = $rows[]. "<a href='puntimodifica.php?id=" . $r["NAME"] . "'>edit <img src='../../famfamfam_silk_icons_v013/icons/database_edit.png' width='16' height='16' alt='' /> </a>";
	}
	pg_close($conn);
	#echo $rows ;
	if (empty($rows)==FALSE){
		//print $rows;
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo "[{\"NOTE\":'No data'}]";
	}
}

?>


