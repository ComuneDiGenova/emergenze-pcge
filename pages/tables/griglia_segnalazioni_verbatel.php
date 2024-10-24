<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');

// Filtro per tipologia di criticità
if(isset($_GET["f"])){
	$getfiltri=$_GET["f"];
	//echo $getfiltri;

	require('./filtri_segnalazioni.php'); //contain the function filtro used in the following line
	$filter=filtro($getfiltri);
}


if(!$conn) {
    die('Connessione fallita !<br />');
} else {

	$query = "SELECT 
		s.id, 
		s.criticita, 
		s.id_evento, 
		s.num, 
		s.in_lavorazione, 
		s.localizzazione, 
		s.nome_munic,
		st_x(s.geom) AS lon, 
		st_y(s.geom) AS lat,
		s.incarichi,
		string_agg(
			CASE 
				WHEN i.id_stato_incarico::varchar = '2' THEN i.descrizione_uo::varchar
				WHEN ii.id_stato_incarico::varchar = '2' THEN ii.descrizione_uo::varchar
				ELSE null
			END, ' - '
		) AS responsabile_incarico,
		coalesce(sdv.intervento_id, 0)::boolean AS from_verbatel,
		bool_and(i.id_stato_incarico = 3 OR ii.id_stato_incarico = 3) AS incarichi_chiusi,
		bool_and(coalesce(sdv.intervento_id, 0)::boolean and (s.id_profilo=6)) as presa_visione_verbatel
	FROM 
		segnalazioni.v_segnalazioni_lista_pp s
	JOIN 
		segnalazioni.join_segnalazioni_in_lavorazione j
		ON s.id_lavorazione = j.id_segnalazione_in_lavorazione
	LEFT JOIN 
		segnalazioni.v_incarichi i
		ON s.id_lavorazione = i.id_lavorazione
	LEFT JOIN 
		segnalazioni.v_incarichi_interni ii
		ON s.id_lavorazione = ii.id_lavorazione
	LEFT JOIN 
		verbatel.segnalazioni_da_verbatel sdv
		ON sdv.segnalazione_id = s.id
	WHERE
		sdv.segnalazione_id is NOT NULL
		AND (s.in_lavorazione = 't' OR s.in_lavorazione IS NULL)
		AND (s.fine_sospensione IS NULL OR s.fine_sospensione < NOW())
		AND j.sospeso = 'f'
	GROUP BY 
		s.id, 
		sdv.intervento_id, 
		s.criticita, 
		s.id_evento, 
		s.num, 
		s.in_lavorazione, 
		s.localizzazione, 
		s.nome_munic, 
		lon, 
		lat, 
		s.incarichi;";

	$result = pg_query($conn, $query);

	$rows = array();
	while($r = pg_fetch_assoc($result)) {
    		$rows[] = $r;
    		//$rows[] = $rows[]. "<a href='puntimodifica.php?id=" . $r["NAME"] . "'>edit <img src='../../famfamfam_silk_icons_v013/icons/database_edit.png' width='16' height='16' alt='' /> </a>";
	}
	pg_close($conn);
	#echo $rows ;
	if (empty($rows)==FALSE){
		// print $rows;
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo '[]';
	}
}

?>


