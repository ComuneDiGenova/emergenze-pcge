<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET["id"];


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT extract(epoch from l.data_ora AT TIME ZONE 'UTC' AT TIME ZONE 'CEST') as data_ora, l.lettura
	 FROM geodb.lettura_idrometri_comune l
	 WHERE id_station = '".$id."' AND data_ora >  (now()- interval '14 days')
	 ORDER BY data_ora asc;";
    
    //echo $query;
	$result = pg_query($conn, $query);
	#echo $query;
	#exit;
	$rows = array();
	$json = '[';
	$check=0;
	while($r = pg_fetch_assoc($result)) {
    		//$rows[] = $r;
			if ($check==0){
				$json= $json . '['.$r['data_ora'].'000,'.max(0,$r['lettura']).']';
    		} else {
				$json= $json . ',['.$r['data_ora'].'000,'.max(0,$r['lettura']).']';
			}
			$check=1;
	}
	$json=$json .']';
	echo $json;
	pg_close($conn);
	#echo $rows ;
	/*if (empty($rows)==FALSE){
		//print $rows;
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo "[{\"NOTE\":'No data'}]";
	}*/
}

?>


