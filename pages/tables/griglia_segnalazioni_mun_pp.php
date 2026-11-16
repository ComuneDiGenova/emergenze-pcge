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
	$query = "SELECT 
						s.id, s.criticita, s.id_evento, sum(s.num) as num, s.in_lavorazione, s.localizzazione, s.nome_munic, 
						st_x(s.geom) as lon, st_y(s.geom) as lat,
						(
							count(i.id_lavorazione) filter (where i.id_stato_incarico = 2) > 0
							OR count(ii.id_lavorazione) filter (where ii.id_stato_incarico = 2) > 0
							OR count(sop.id) filter (where sop.id_stato_sopralluogo IN (1, 2)) > 0
						) AS incarichi,
						(
							(count(i.id_lavorazione) + count(ii.id_lavorazione) + count(sop.id)) > 0
							AND
							(
								(count(i.id_lavorazione) filter (where i.id_stato_incarico <> 3))
								+ (count(ii.id_lavorazione) filter (where ii.id_stato_incarico <> 3))
								+ (count(sop.id) filter (where sop.id_stato_sopralluogo <> 3))
							) = 0
						) AS incarichi_chiusi,
						string_agg(DISTINCT CASE
							WHEN i.id_stato_incarico IN (1, 2) THEN i.descrizione_uo::varchar
							WHEN ii.id_stato_incarico IN (1, 2) THEN ii.descrizione_uo::varchar
							ELSE NULL
						END, ' - ') AS responsabile_incarico,
						string_agg(DISTINCT CASE
							WHEN sop.id_stato_sopralluogo IN (1, 2) THEN sop.descrizione_uo::varchar
							ELSE NULL
						END, ' - ') AS responsabile_presidio
					from segnalazioni.v_segnalazioni_lista_pp s
					join segnalazioni.join_segnalazioni_in_lavorazione j 
						on s.id_lavorazione=j.id_segnalazione_in_lavorazione
					left join segnalazioni.v_incarichi i
						on s.id_lavorazione=i.id_lavorazione
					left join segnalazioni.v_incarichi_interni ii
						on s.id_lavorazione=ii.id_lavorazione
					left join segnalazioni.v_sopralluoghi_last_update sop
						on sop.id_lavorazione = s.id_lavorazione and sop.id_stato_sopralluogo < 4
					where (s.in_lavorazione = 't' or s.in_lavorazione is null) 
						and (s.fine_sospensione is null OR s.fine_sospensione < now()) 
						and j.sospeso='t'
					group by s.id, s.criticita, s.id_evento,
							s.in_lavorazione, s.localizzazione, 
							s.nome_munic, s.geom;";
    
	$result = pg_query($conn, $query);

	$rows = array();
	while($r = pg_fetch_assoc($result)) {
    		$rows[] = $r;
	}

	pg_close($conn);

	if (empty($rows)==FALSE){
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo '[]';
	}
}

?>
