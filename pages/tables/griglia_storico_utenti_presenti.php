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
	//$idcivico=$_GET["id"];
	$query="SELECT u.matricola_cf,
			concat(u.descrizione, ' - ' , u.nome_munic) as profilo,
			u.nome,
			u.cognome,
			u.id_profilo,
			u.telegram_id,
			u.telegram_attivo,
			tp.operativo,
			tp.data_inizio,
			tp.durata,
			tp.data_fine,
			tp.id
		FROM \"users\".\"v_utenti_sistema\" u
			LEFT JOIN users.t_presenze tp ON u.telegram_id::text = tp.id_telegram::text
		WHERE tp.operativo = false
		GROUP BY u.matricola_cf, u.nome, u.cognome, u.id_profilo, u.telegram_id, u.telegram_attivo, tp.operativo, tp.data_inizio, tp.durata, tp.data_fine, tp.id, u.descrizione, u.nome_munic;";
    $result = pg_prepare($conn, "myquery0", $query);
	$result = pg_execute($conn, "myquery0", array());
    //echo $query;
	//$result = pg_query($conn, $query);
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


