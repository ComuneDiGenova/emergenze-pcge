<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET["id"];


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$perc='perc_al_g';
	if ($perc!=''){
		$query="SELECT ".$perc." 
		FROM geodb.punti_monitoraggio_ok 
		GROUP BY ".$perc." 
		ORDER BY ".$perc.";";
		//echo $query;
		$result = pg_query($conn, $query);
		#echo $query;
		#exit;
		$rows = array();
		$json = '[';
		$check=0;
		while($r = pg_fetch_assoc($result)) {
			if($check>0){
				echo ',';
			}
			echo '"'.$r["$perc"].'":[';
			$query2 = "SELECT SELECT p.id, concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome
					FROM geodb.punti_monitoraggio_ok p
					WHERE p.id is not null and $perc = '".$r["$perc"]."'  
					order by nome;";
			$result2 = pg_query($conn, $query2);
			while($r = pg_fetch_assoc($result)) {
				echo $r2
			}
			echo ']';
			$check=$check+1;
		}
	} else {
		
	}
	
	//$json=$json .']';
	//echo $json;
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


