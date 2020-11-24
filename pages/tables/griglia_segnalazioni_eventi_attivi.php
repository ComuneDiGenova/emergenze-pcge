<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');


if ($check_evento==1){
	$len=count($eventi_attivi);	               
	for ($i=0;$i<$len;$i++){
		if ($i==0){
			$filter = "id_evento = ".$eventi_attivi[$i];
		} else {
			$filter = $filter. " OR id_evento = ". $eventi_attivi[$i];
		}
	}
}

//echo $filter;



// Filtro per tipologia di criticità
$getfiltri=$_GET["f"];
//echo $getfiltri;
$filtro_municipio=$_GET["m"];
$filtro_from=$_GET["from"];
$filtro_to=$_GET["to"];
$resp=$_GET["r"];


require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
//$filtro_c=filtro($getfiltri);
//$filtro_c=filtro2($getfiltri,$filtro_municipio);
$filtro_c=filtro2($getfiltri, $filtro_municipio,$filtro_from,$filtro_to)[0];


if ($filtro_c=='' and $filter!=''){
	$filter_completo = " WHERE ".$filter;
} else if ($filtro_c != '' and $filter!=''){
	$filter_completo = $filtro_c." AND (".$filter .")" ;
} else if ($filtro_c!='' and $filter==''){
	$filter_completo = $filtro_c;
}


if (strlen($resp)>1){
	if ($filter_completo !='') {
		$filter_r= ' AND ';
	} else {
		$filter_r= ' WHERE ';
	}
	$filter_r = $filter_r ." id_profilo='".$resp."'";
}

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT id, id as id2, data_ora, descrizione, criticita, 
       rischio, id_evento, tipo_evento, id_man,
       note, in_lavorazione, localizzazione, nome_munic, st_x(geom) as lon, st_y(geom) as lat FROM segnalazioni.v_segnalazioni_lista ".$filter_completo." ".$filter_r." ;";
	
	// vecchia query per evento attivo.
	/*$query="SELECT id, data_ora, id_segnalante, descrizione, id_criticita, criticita, 
       rischio, id_evento, id_civico, id_municipio, id_operatore, 
       note, lavorazione From segnalazioni.v_segnalazioni WHERE ".$filter .";";*/
    
    //echo $query ."<br>";
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


