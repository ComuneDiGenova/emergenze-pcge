<?php
session_start();
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
$profilo=$_GET['p'];
$livello=$_GET['l'];
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
	$query="SELECT matricola_cf, concat(descrizione, ' - ' , nome_munic) as profilo, valido,  nome, cognome From \"users\".\"v_utenti_sistema\" ".$filter." ORDER BY \"cognome\", \"nome\" ;";
    
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


