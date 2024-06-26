<?php
session_start();
//require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
$profilo=(int)pg_escape_string($_GET['p']);
$livello=pg_escape_string($_GET['l']);
if ($profilo==3){
	$filter = ' ';
} else if($profilo==8){
	$filter= ' WHERE id_profilo=\''.$profilo.'\' and nome_munic = \''.$livello.'\' ';
} else {
	$filter= ' WHERE id_profilo=\''.$profilo.'\' ';
}


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	$query="SELECT DISTINCT ON (u.telegram_id) u.matricola_cf,
								u.nome,
								u.cognome,
								jtfc.funzione,
								u.telegram_id,
								tp.data_invio,
								tp.lettura,
								tp.data_conferma,
								tp.data_invio_conv,
								tp.data_conferma_conv,
								tp.lettura_conv 
   			FROM users.utenti_coc u
			JOIN users.t_convocazione tp 
				ON u.telegram_id::text = tp.id_telegram::text
			JOIN users.tipo_funzione_coc jtfc 
				ON jtfc.id = u.funzione
			WHERE tp.data_invio_conv IS NOT null
			ORDER BY u.telegram_id, tp.data_invio DESC, tp.data_invio_conv DESC;";

    $result = pg_prepare($conn, "myquery0", $query);
	$result = pg_execute($conn, "myquery0", array());

	$rows = array();
	while($r = pg_fetch_assoc($result)) {
    		$rows[] = $r;
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


