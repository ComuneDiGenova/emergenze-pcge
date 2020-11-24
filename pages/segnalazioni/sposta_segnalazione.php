<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');


//$id=$_GET["id"];
$id=$_GET["id"];

$query= "SELECT geom FROM segnalazioni.t_segnalazioni WHERE id=".$id.";";
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$old_geom=$r["geom"];
	echo $old_geom;
}


$note_geo= str_replace("'", "''", $_POST["note_geo"]);







if ($_POST["id_civico"]!=''){
 	$query_civico= 'SELECT st_transform (geom,4326) as geom FROM geodb.civici where id='.$_POST["id_civico"].';';
 	echo $query_civico;
 	echo "<br>";
 	// se ci fossero problemi con il valore 'geom' controlla l record corrispondente nella tabella geodb.m_tables, 
	// che gestisce il trasferimento dati da Oracle a postgis
 	$result_civico=pg_query($conn, $query_civico);
	while($rc = pg_fetch_assoc($result_civico)) {
		$geom="'".$rc["geom"]."'"; // messo fra apici per poi includerlo nella successiva query	
	}

} else if($_POST["lat"]!='') {
	$geom="ST_GeomFromText('POINT(".$_POST["lon"]." ".$_POST["lat"].")',4326)";

} else {
	echo "ERROR: geometria non definita<br>";
	exit;
}


$query_municipio="select codice_mun from geodb.municipi WHERE st_intersects(st_transform(geom,4326),".$geom.");";
	$result_m = pg_query($conn, $query_municipio);
      //echo $query1;    
      while($r_m = pg_fetch_assoc($result_m)) {
      	$municipio=$r_m['codice_mun'];
      }

echo "<br>";



// update t_segnalazioni
$query="UPDATE segnalazioni.t_segnalazioni set geom=".$geom." , 
 id_municipio=".$municipio." ";

if ($note_geo!=''){
	$query=$query. ", note='".$note_geo."'";
}

if ($_POST["id_civico"]!=''){
	$query=$query. ", id_civico='".$_POST["id_civico"]."'";
} else {
	$query=$query. ", id_civico=NULL";
}

$query=$query." WHERE id=".$id."; "; 

echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


//exit;
//salvo lo storico
$query="INSERT INTO segnalazioni.t_spostamento_segnalazioni(
	id_segnalazione, old_geom)
	VALUES (".$id.",'".$old_geom.  "');";

echo $query;
$result = pg_query($conn, $query);
echo "<br>";


//exit;
$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Spostamento segnalazione ".$id."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_segnalazione.php?id=".$id);


?>