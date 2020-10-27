<?php 
if($id_civico !='') {
	$queryc= "SELECT * FROM geodb.civici WHERE id=".$id_civico.";";
	$resultc=pg_query($conn, $queryc);
	while($rc = pg_fetch_assoc($resultc)) {
		echo "<b>Indirizzo civico</b>:" .$rc['desvia'].", ".$rc['testo'].", ".$rc['cap'];
		echo "<br><b>Municipio</b>:" .$rc['desmunicipio'];
	}
} else {
	$queryc= "SELECT desvia, testo, cap, st_distance(st_transform(geom,4326),'".$geom."') as distance  
	FROM geodb.civici 
	where codvia= (SELECT codvia 
	FROM geodb.v_vie_unite 
	ORDER BY st_distance(st_transform(geom,4326),'".$geom."') LIMIT 1)ORDER BY distance LIMIT 1;";
	//echo $queryc;
	$resultc=pg_query($conn, $queryc);
	while($rc = pg_fetch_assoc($resultc)) {
		echo "<b>Indirizzo civico (segnalazione non precisa, indirizzo di prossimità ricavato automaticamente)</b>:" .$rc['desvia'].", ".$rc['testo'].", ".$rc['cap'];
		//echo "<br><b>Municipio</b>:" .$rc['desmunicipio'];
	}
	$queryc= "SELECT nome_munic FROM geodb.municipi WHERE codice_mun='".$id_municipio."';";
	//echo $queryc;
	$resultc=pg_query($conn, $queryc);
	while($rc = pg_fetch_assoc($resultc)) {
		echo "<br><b>Municipio</b>:" .$rc['nome_munic'];
		//echo "<br><b>Municipio</b>:" .$rc['desmunicipio'];
	}
}
?>