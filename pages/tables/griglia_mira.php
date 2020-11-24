<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET["id"];


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT l.id_lettura, l.data_ora, l.data_ora_reg , max(m.data_ora_mod) as data_ora_mod
	 FROM geodb.lettura_mire l
	 LEFT JOIN geodb.lettura_mire_modifiche m ON m.data_ora=l.data_ora and m.num_id_mira = l.num_id_mira 
	 WHERE l.num_id_mira = ".$id."
	 GROUP BY l.id_lettura, l.data_ora, l.data_ora_reg
	 ORDER BY data_ora desc;";
    
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


