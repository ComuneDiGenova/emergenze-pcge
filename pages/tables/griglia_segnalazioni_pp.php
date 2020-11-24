<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');

// Filtro per tipologia di criticità
if(isset($_GET["f"])){
	$getfiltri=$_GET["f"];
	//echo $getfiltri;

	require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
	$filter=filtro($getfiltri);
}


$filter= " WHERE (s.in_lavorazione = 't' or s.in_lavorazione is null) and (s.fine_sospensione is null OR s.fine_sospensione < now()) ";


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT s.id, s.criticita, s.id_evento,
       s.num, s.in_lavorazione, s.localizzazione, s.nome_munic, st_x(s.geom) as lon, st_y(s.geom) as lat, s.incarichi 
       FROM segnalazioni.v_segnalazioni_lista_pp s
       JOIN segnalazioni.join_segnalazioni_in_lavorazione j ON s.id_lavorazione=j.id_segnalazione_in_lavorazione ".$filter." and j.sospeso='f';";
     
   //echo $query;
	//echo "<br>";
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


