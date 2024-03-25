<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');

// Filtro per tipologia di criticità
if(isset($_GET["f"])){
	$getfiltri=$_GET["f"];

	require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
	$filter=filtro($getfiltri);
}


if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	$query="SELECT s.id, s.criticita, s.id_evento, s.num, s.in_lavorazione, s.localizzazione, s.nome_munic, 
					st_x(s.geom) as lon, st_y(s.geom) as lat, 
					s.incarichi, s.id_profilo,
					string_agg(case when i.id_stato_incarico::varchar = '2' then i.descrizione_uo::varchar 
									when ii.id_stato_incarico::varchar = '2' then ii.descrizione_uo::varchar
									else null end, ' - ') as responsabile_incarico
			FROM segnalazioni.v_segnalazioni_lista_pp s
			JOIN segnalazioni.join_segnalazioni_in_lavorazione j 
				ON s.id_lavorazione=j.id_segnalazione_in_lavorazione
			LEFT JOIN segnalazioni.v_incarichi i
				ON s.id_lavorazione=i.id_lavorazione
			LEFT JOIN segnalazioni.v_incarichi_interni ii
				ON s.id_lavorazione=ii.id_lavorazione
			WHERE (s.in_lavorazione = 't' OR s.in_lavorazione is null) 
				AND (s.fine_sospensione is null OR s.fine_sospensione < now()) 
				AND j.sospeso='t'
			GROUP BY s.id, s.criticita, s.id_evento,
					s.num, s.in_lavorazione, s.localizzazione, 
					s.nome_munic, lon, lat, s.incarichi, s.id_profilo;";
    
	$result = pg_query($conn, $query);

	$rows = array();
	while($r = pg_fetch_assoc($result)) {
    		$rows[] = $r;
	}

	pg_close($conn);

	if (empty($rows)==FALSE){
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo "[{\"NOTE\":'No data'}]";
	}
}

?>
