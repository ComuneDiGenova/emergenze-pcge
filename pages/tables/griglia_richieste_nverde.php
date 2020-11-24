<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT id_evento, to_char(r.data_ora, 'YYYY/MM/DD HH24:MI:SS'::text) AS data_ora,
r.descrizione, concat (s.nome_cognome, ' ' , s.telefono,' ', s.note) as segnalante,
t.descrizione as tipo_segnalante
	FROM segnalazioni.t_richieste_nverde r
	JOIN segnalazioni.t_segnalanti s ON r.id_segnalante=s.id
	JOIN segnalazioni.tipo_segnalanti t ON t.id=s.id_tipo_segnalante
	JOIN eventi.t_eventi e ON e.id = r.id_evento
	 WHERE e.valido = true OR e.valido IS NULL
  ORDER BY r.data_ora desc, r.id_evento;";
    
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


