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
	$query = "SELECT main.id, main.criticita, main.id_evento, main.num, main.in_lavorazione, main.localizzazione, main.nome_munic, 
					main.lon, main.lat,
					main.incarichi, string_agg(main.responsabile_incarico, ' - ') AS responsabile_incarico
				FROM (
					select s.id, s.criticita, s.id_evento,sum(s.num) as num, s.in_lavorazione, s.localizzazione, s.nome_munic, 
						st_x(s.geom) as lon, st_y(s.geom) as lat,
						s.incarichi,
						unnest(
							array_agg(distinct case 
													when i.id_stato_incarico = 1 then i.descrizione_uo::varchar 
													when i.id_stato_incarico = 2 then i.descrizione_uo::varchar
												end) || 
							array_agg(distinct case 
													when ii.id_stato_incarico = 1 then ii.descrizione_uo::varchar
													when ii.id_stato_incarico = 2 then ii.descrizione_uo::varchar
												end)
						) as responsabile_incarico		
					from segnalazioni.v_segnalazioni_lista_pp s
					join segnalazioni.join_segnalazioni_in_lavorazione j 
						on s.id_lavorazione=j.id_segnalazione_in_lavorazione
					left join segnalazioni.v_incarichi i
						on s.id_lavorazione=i.id_lavorazione
					left join segnalazioni.v_incarichi_interni ii
						on s.id_lavorazione=ii.id_lavorazione
					where (s.in_lavorazione = 't' or s.in_lavorazione is null) 
						and (s.fine_sospensione is null OR s.fine_sospensione < now()) 
						and j.sospeso='t'
					group by s.id, s.criticita, s.id_evento,
							s.num, s.in_lavorazione, s.localizzazione, 
							s.nome_munic, lon, lat, s.incarichi) AS main
				GROUP BY main.id, main.criticita, main.id_evento, main.num, main.in_lavorazione, main.localizzazione, 
						main.nome_munic, lon, lat, main.incarichi;";
    
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
