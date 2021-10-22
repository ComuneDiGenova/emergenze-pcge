<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	// $query="SELECT id, matricola_cf, nome, cognome, mail, telegram_id
	// FROM users.utenti_coc ORDER BY cognome ;";
	$query="SELECT uc.id, matricola_cf, nome, cognome, mail, telegram_id, jtfc.funzione
	FROM users.utenti_coc uc
	join users.tipo_funzione_coc jtfc on jtfc.id = uc.funzione
	ORDER BY cognome;";
	$result = pg_prepare($conn, "myquery0", $query);
	$result = pg_execute($conn, "myquery0", array());
    
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


