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
    u.nome,
    u.cognome,
    u.telegram_id,
    tp.data_invio::timestamp::date,
	tp.data_invio::timestamp::time as ora_invio,
    tp.lettura,
    tp.data_conferma
   	FROM users.utenti_coc u
    right JOIN users.t_convocazione tp ON u.telegram_id::text = tp.id_telegram::text
  	WHERE tp.data_invio < (select max(tp.data_invio) FROM users.t_convocazione tp) 
  	GROUP BY u.matricola_cf, u.nome, u.cognome, u.telegram_id, tp.lettura, tp.data_conferma, tp.data_invio 
 	order by tp.data_invio desc;";
	// $query="SELECT u.matricola_cf,
	// u.nome,
	// u.cognome,
	// u.telegram_id,
	// tp.data_invio,
	// tp.lettura,
	// tp.data_conferma
	// FROM users.utenti_coc u
	// right JOIN users.t_convocazione tp ON u.telegram_id::text = tp.id_telegram::text
	// order by tp.data_invio desc";
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


