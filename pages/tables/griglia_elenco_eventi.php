<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT e.id, e.descrizione, 
	to_char(e.data_ora_inizio_evento, 'YYYY/MM/DD HH24:MI'::text) AS data_ora_inizio_evento, 
	to_char(e.data_ora_fine_evento, 'YYYY/MM/DD HH24:MI'::text) AS data_ora_fine_evento, 
	e.valido, n.nota 
	From eventi.v_eventi e 
	LEFT JOIN eventi.t_note_eventi n ON n.id_evento = e.id 
	ORDER by data_ora_inizio_evento desc;";
    
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


