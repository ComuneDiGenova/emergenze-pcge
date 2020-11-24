<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

require('../check_evento.php');




if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	//$query="SELECT matricola, concat(cognome, ' ', nome, ' (',settore,' - ', ufficio) as nome FROM varie.v_dipendenti v ORDER BY cognome;";
    
   $query="SELECT v.matricola as matricola_cf, concat(v.cognome, ' ', v.nome, ' (', v.settore,ufficio,')') 
   	as nome FROM varie.v_dipendenti v 
   	WHERE NOT EXISTS
		(SELECT matricola_cf FROM users.v_componenti_squadre s WHERE s.matricola_cf = v.matricola and data_end is null) 
		ORDER BY cognome";
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


