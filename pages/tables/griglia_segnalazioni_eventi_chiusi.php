<?php
session_start();
include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';

//require('../check_evento.php');


// Filtro per tipologia di criticità
$getfiltri=$_GET["f"];
$filtro_evento_attivo=$_GET["a"];
$filtro_municipio=$_GET["m"];

//echo $getfiltri;

require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
#$filter=filtro($getfiltri);
$filter=filtro2($getfiltri, $filtro_municipio);



if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT id, data_ora, id_segnalante, descrizione, id_criticita, criticita, 
       rischio, id_evento, tipo_evento, id_civico, id_municipio, id_operatore, 
       note, id_lavorazione, in_lavorazione, localizzazione, nome_munic, st_x(geom) as lon, st_y(geom) as lat 
       FROM segnalazioni.v_segnalazioni_eventi_chiusi_lista ".$filter[0]." ;";
   //echo $query;
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


