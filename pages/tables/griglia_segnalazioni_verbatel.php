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
		MAX(seg.id) AS id,
		COUNT(seg.id) AS num,
		STRING_AGG(seg.descrizione::text, '<br>') AS descrizione,
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT crit.descrizione::text), '<br>') AS criticita,
		ARRAY_TO_STRING(ARRAY_AGG(DISTINCT mun.nome_munic::text), '<br>') AS municipio,
		STRING_AGG(
			COALESCE(
				(civ.desvia || ' ' || civ.testo),
				civici_laterale.desvia_testo
			), '<br>'
		) AS localizzazione,
		join_lav.id_segnalazione_in_lavorazione AS id_lavorazione,
		lav.in_lavorazione,
		lav.id_profilo,
		COALESCE(
			count(inc_esterni.id_lavorazione) filter (where inc_esterni.id_stato_incarico<3)>0, 
			count(inc_interni.id_lavorazione) filter (where inc_interni.id_stato_incarico<3)>0, 
			count(prov_caut.id_lavorazione) filter (where prov_caut.id_stato_provvedimenti_cautelari<3)>0, 
			count(sopralluoghi.id_lavorazione) filter (where sopralluoghi.id_stato_sopralluogo<3)>0
		) AS incarichi,
		COALESCE(
			count(inc_esterni.id_lavorazione) filter (where inc_esterni.id_stato_incarico=3)>1, 
			count(inc_interni.id_lavorazione) filter (where inc_interni.id_stato_incarico=3)>0, 
			count(prov_caut.id_lavorazione) filter (where prov_caut.id_stato_provvedimenti_cautelari=3)>0, 
			count(sopralluoghi.id_lavorazione) filter (where sopralluoghi.id_stato_sopralluogo=3)>0
		) AS incarichi_chiusi,
		seg.id_evento,
		MAX(seg.geom::text) AS geom,
		evento.fine_sospensione,
		string_agg(
			CASE 
				WHEN inc_esterni.id_stato_incarico::varchar = '2' THEN inc_esterni.descrizione_uo::varchar
				WHEN inc_interni.id_stato_incarico::varchar = '2' THEN inc_interni.descrizione_uo::varchar
				ELSE null
			END, ' - '
		) AS responsabile_incarico,
		bool_and(coalesce(verb.intervento_id, 0)::boolean and (lav.id_profilo=6)) as presa_visione_verbatel
	FROM 
		segnalazioni.t_segnalazioni seg
	JOIN segnalazioni.tipo_criticita crit ON crit.id = seg.id_criticita
	JOIN eventi.t_eventi evento ON evento.id = seg.id_evento
	LEFT JOIN segnalazioni.join_segnalazioni_in_lavorazione join_lav ON join_lav.id_segnalazione = seg.id
	LEFT JOIN segnalazioni.t_segnalazioni_in_lavorazione lav ON join_lav.id_segnalazione_in_lavorazione = lav.id
	LEFT JOIN geodb.municipi mun ON seg.id_municipio = mun.id::integer
	LEFT JOIN geodb.civici civ ON civ.id = seg.id_civico
	LEFT JOIN LATERAL (
		SELECT CONCAT('~ ', civ_est.desvia, ' ', civ_est.testo) AS desvia_testo
		FROM geodb.civici civ_est
		WHERE civ_est.geom && ST_Expand(ST_Transform(seg.geom, 3003), 250)
		ORDER BY ST_Distance(civ_est.geom, ST_Transform(seg.geom, 3003))
		LIMIT 1
	) civici_laterale ON civ.id IS NULL
	LEFT JOIN segnalazioni.v_incarichi_last_update inc_esterni 
		ON inc_esterni.id_lavorazione = join_lav.id_segnalazione_in_lavorazione AND inc_esterni.id_stato_incarico < 4
	LEFT JOIN segnalazioni.v_incarichi_interni_last_update inc_interni 
		ON inc_interni.id_lavorazione = join_lav.id_segnalazione_in_lavorazione AND inc_interni.id_stato_incarico < 4
	LEFT JOIN segnalazioni.v_provvedimenti_cautelari_last_update prov_caut 
		ON prov_caut.id_lavorazione = join_lav.id_segnalazione_in_lavorazione AND prov_caut.id_stato_provvedimenti_cautelari < 4
	LEFT JOIN segnalazioni.v_sopralluoghi_last_update sopralluoghi 
		ON sopralluoghi.id_lavorazione = join_lav.id_segnalazione_in_lavorazione AND sopralluoghi.id_stato_sopralluogo < 4
	LEFT JOIN verbatel.segnalazioni_da_verbatel verb ON verb.segnalazione_id = seg.id
	WHERE lav.in_lavorazione = true
		AND verb.segnalazione_id IS NOT NULL
	GROUP BY 
		join_lav.id_segnalazione_in_lavorazione, 
		lav.in_lavorazione, 
		lav.id_profilo, 
		seg.id_evento, 
		evento.fine_sospensione
	ORDER BY lav.in_lavorazione DESC;";

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