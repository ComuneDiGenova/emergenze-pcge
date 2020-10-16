<?php
session_start();
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query = "SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, 'Coordinamento sala emergenze' as tipo, ";
	$query = $query. "'t_coordinamento' as table FROM report.t_coordinamento r ";
	$query = $query. "JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola UNION ";
	
	$query = $query. "SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, 'Monitoraggio meteo' as tipo, ";
	$query = $query. "'t_monitoraggio_meteo' as table FROM report.t_monitoraggio_meteo r ";
	$query = $query. "JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola UNION ";
	
	$query = $query. "SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, 'Tecnico protezione civile' as tipo, ";
	$query = $query. "'t_tecnico_pc' as table FROM report.t_tecnico_pc r ";
	$query = $query. "JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola UNION ";
	
	$query = $query. "SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, 'Operatore presidi territoriali meteo' as tipo, ";
	$query = $query. "'t_presidio_territoriale' as table FROM report.t_presidio_territoriale r ";
	$query = $query. "JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola UNION ";
	
	$query = $query. "SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, 'Operatore n verde' as tipo, ";
	$query = $query. "'t_operatore_nverde' as table FROM report.t_operatore_nverde r ";
	$query = $query. "JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola UNION ";
	
	$query = $query. "SELECT r.id, r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, 'Operatore gestione volontari' as tipo, ";
	$query = $query. "'t_operatore_volontari' as table FROM report.t_operatore_volontari r ";
	$query = $query. "JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf";
	
	
	
	$query = $query. " order by data_start desc, cognome;";
    
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



