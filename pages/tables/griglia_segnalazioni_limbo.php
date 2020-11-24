<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');




//echo $filter;



$filter_completo =" ";
// Filtro per tipologia di criticità
if(isset($_GET["f"])){
	$getfiltri=$_GET["f"];
}
//echo $getfiltri;
require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
$filtro_c=filtro($getfiltri);

if ($filtro_c=='' and isset($filter)){
	$filter_completo = " WHERE ".$filter." and in_lavorazione is null";
} else if ($filtro_c != '' and isset($filter)){
	$filter_completo = $filtro_c." AND (".$filter .") and in_lavorazione is null" ;
} else if ($filtro_c!='' and $filter==''){
	$filter_completo = $filtro_c ." and in_lavorazione is null";
} else if ($filtro_c=='' and $filter==''){
	$filter_completo = " WHERE in_lavorazione is null and (fine_sospensione is null OR fine_sospensione < now())";
}



if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT id, id_evento, data_ora, descrizione, criticita, 
       rischio, note, id_municipio, id_operatore, localizzazione, nome_munic, st_x(geom) as lon, st_y(geom) as lat 
       FROM segnalazioni.v_segnalazioni_lista ".$filter_completo." ;";
	//echo $query;
	// vecchia query per evento attivo.
	/*$query="SELECT id, data_ora, id_segnalante, descrizione, id_criticita, criticita, 
       rischio, id_evento, id_civico, id_municipio, id_operatore, 
       note, lavorazione From segnalazioni.v_segnalazioni WHERE ".$filter .";";*/
    
   //echo $query;
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


