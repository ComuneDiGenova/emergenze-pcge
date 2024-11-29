<?php
	session_start();
	require('../validate_input.php');
	include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

	if(!$conn) {
		die('Connessione fallita !<br />');
	
	}

	$query="SELECT e.id, e.descrizione, 
					to_char(e.data_ora_inizio_evento, 'YYYY/MM/DD HH24:MI'::text) AS data_ora_inizio_evento, 
					to_char(e.data_ora_fine_evento, 'YYYY/MM/DD HH24:MI'::text) AS data_ora_fine_evento, 
					e.valido, n.nota 
			FROM eventi.v_eventi e 
			LEFT JOIN eventi.t_note_eventi n ON n.id_evento = e.id 
			ORDER by data_ora_inizio_evento DESC;";
	
	$result = pg_query($conn, $query);

	$rows = array();
	while($r = pg_fetch_assoc($result)) {
			$rows[] = $r;
	}
	pg_close($conn);

	if (empty($rows)==FALSE){
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo '[{"NOTE":"No data"}]';
	}

?>


