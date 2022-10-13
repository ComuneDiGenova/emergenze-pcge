<?php
session_start();
//require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');

// Filtro per tipologia di criticità
$getfiltri=pg_escape_string($_GET["f"]);
//echo $getfiltri;


if ($getfiltri == 'prima_pagina'){
	$filter = ' AND id_stato_sopralluogo=2 ';
} else {
	require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
	$filter=filtro($getfiltri);
}



$filtro_from=pg_escape_string($_GET["from"]);
$filtro_to=pg_escape_string($_GET["to"]);


//require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
//$filter=filtro($getfiltri);


if (strlen($filtro_from)>=12 || strlen($filtro_to)>=12){
		$check2=1;
	}
	
	if ($check2==1) {
		$filter = $filter . " AND (" ;
	}
	
	if (strlen($filtro_from)>=12 ) {
		$filter = $filter . " TO_TIMESTAMP(data_ora_invio, 'DD/MM/YYYY HH24:MI:SS') > '".$filtro_from."' ";
	}
	
	if (strlen($filtro_from)>=12 && strlen($filtro_to)>=12) {
		$filter = $filter . " AND " ;
	}
	
	if (strlen($filtro_to)>=12) {
		$filter = $filter . " TO_TIMESTAMP(data_ora_invio, 'DD/MM/YYYY HH24:MI:SS') < '".$filtro_to."' ";
	}
	
	if ($check2==1){
		$filter = $filter . ")" ;
	}


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT p.id, p.descrizione, p.descrizione_uo, p.data_ora_invio,
	p.id_stato_sopralluogo, s.componenti From segnalazioni.v_sopralluoghi_mobili_last_update p 
	left join users.v_squadre s ON s.id=p.id_squadra where id_stato_sopralluogo < 3 ".$filter.";";
    
   //echo $query . "<br>";
	$result = pg_query($conn, $query);
	#echo $query;
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


