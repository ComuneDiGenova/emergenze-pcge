<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');

// Filtro per tipologia di criticità
$getfiltri=$_GET["f"];
//echo $getfiltri;

$filtro_from=$_GET["from"];
$filtro_to=$_GET["to"];

$resp=$_GET["r"];
$uo=$_GET["u"];

//require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
//$filter=filtro($getfiltri);


if (strlen($filtro_from)>=12 || strlen($filtro_to)>=12){
		$check2=1;
	}
	
	if ($check2==1) {
		$filter = $filter . " AND (" ;
	}
	
	if (strlen($filtro_from)>=12 ) {
		$filter = $filter . " TO_TIMESTAMP(data_ora_invio, 'DD/MM/YYYY HH24:MI:SS') > ".$filtro_from." ";
	}
	
	if (strlen($filtro_from)>=12 && strlen($filtro_to)>=12) {
		$filter = $filter . " AND " ;
	}
	
	if (strlen($filtro_to)>=12) {
		$filter = $filter . " TO_TIMESTAMP(data_ora_invio, 'DD/MM/YYYY HH24:MI:SS') < ".$filtro_to." ";
	}
	
	if ($check2==1){
		$filter = $filter . ")" ;
	}

if(strlen($resp)>=1) {
	$filter = $filter . " and id_profilo='".$resp."' " ;
}

if(strlen($uo)>=1) {
	$filter = $filter . " and id_uo='".$uo."' " ;
}




if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT p.id_stato_incarico, p.data_ora_invio, p.id_profilo, p.id_evento, p.descrizione, p.time_preview, p.time_start,p.time_stop, 
		p.id, max(p.id_segnalazione) as id_segnalazione, s.componenti, s.nome From segnalazioni.v_incarichi_interni_last_update p
		left join users.v_squadre s ON s.id::text=p.id_squadra::text where id_stato_incarico < 3 ".$filter." 
		group by p.id_stato_incarico,  p.data_ora_invio, p.id_profilo, p.id_evento, p.descrizione, p.time_preview, p.time_start,p.time_stop, 
		p.id, s.componenti, s.nome;";
    
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


