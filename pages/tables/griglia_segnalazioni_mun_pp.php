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
	$query="SELECT main.id, main.criticita, main.id_evento, main.num, main.in_lavorazione, main.localizzazione, main.nome_munic, 
					main.lon, main.lat, main.incarichi, 
					STRING_AGG(main.responsabile_incarico, ' - ') AS responsabile_incarico
				FROM (SELECT s.id, s.criticita, s.id_evento, s.num, s.in_lavorazione, s.localizzazione, s.nome_munic, 
							ST_X(s.geom) AS lon, ST_Y(s.geom) AS lat,s.incarichi,
							ARRAY_TO_STRING(
								ARRAY(
									SELECT UNNEST(array_agg(DISTINCT i.descrizione_uo::varchar) || array_agg(DISTINCT ii.descrizione_uo::varchar))
								),
								' - '
							) AS responsabile_incarico
						FROM segnalazioni.v_segnalazioni_lista_pp s
						JOIN segnalazioni.join_segnalazioni_in_lavorazione j 
							ON s.id_lavorazione = j.id_segnalazione_in_lavorazione
						LEFT JOIN segnalazioni.v_incarichi i 
							ON s.id_lavorazione = i.id_lavorazione
						LEFT JOIN segnalazioni.v_incarichi_interni ii 
							ON s.id_lavorazione = ii.id_lavorazione
						WHERE (s.in_lavorazione = 't' OR s.in_lavorazione IS NULL) 
							AND (s.fine_sospensione IS NULL OR s.fine_sospensione < NOW()) 
							AND j.sospeso = 'f'
						GROUP BY s.id, s.criticita,	s.id_evento, s.num, s.in_lavorazione, s.localizzazione, s.nome_munic, 
								lon, lat, s.incarichi) 
								AS main
				GROUP BY main.id, main.criticita, main.id_evento, main.num, main.in_lavorazione, main.localizzazione, main.nome_munic, 
							main.lon, main.lat, main.incarichi;";
    
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
