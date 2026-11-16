<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	// Bootstrap Table server-side params
	$limit  = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 75;
	$offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
	$search = isset($_GET['search']) ? trim((string)$_GET['search']) : '';
	$sort   = isset($_GET['sort']) ? (string)$_GET['sort'] : '';
	$order  = isset($_GET['order']) ? strtolower((string)$_GET['order']) : 'asc';

	$filterRaw = isset($_GET['filter']) ? (string)$_GET['filter'] : '';
	$filter = [];
	if ($filterRaw !== '') {
		$decoded = json_decode($filterRaw, true);
		if (is_array($decoded)) {
			$filter = $decoded;
		}
	}

	$allowedSort = [
		'nome' => 'nome',
		'tipo' => 'tipo',
		'last_update' => 'last_update',
		'perc_al_g' => 'perc_al_g',
		'perc_al_a' => 'perc_al_a',
		'perc_al_r' => 'perc_al_r',
	];
	$sortSql = $allowedSort[$sort] ?? 'nome';
	$orderSql = ($order === 'desc') ? 'DESC' : 'ASC';

	$where = [];

	// filter-control per colonna
	$filterableEquals = ['tipo', 'perc_al_g', 'perc_al_a', 'perc_al_r'];
	foreach ($filterableEquals as $k) {
		if (isset($filter[$k]) && trim((string)$filter[$k]) !== '') {
			$v = trim((string)$filter[$k]);
			$where[] = "$k = " . pg_escape_literal($conn, $v);
		}
	}
	if (isset($filter['nome']) && trim((string)$filter['nome']) !== '') {
		$v = trim((string)$filter['nome']);
		$where[] = "nome ILIKE " . pg_escape_literal($conn, '%' . $v . '%');
	}

	// search globale
	if ($search !== '') {
		$s = pg_escape_literal($conn, '%' . $search . '%');
		$where[] = "(nome ILIKE $s OR tipo ILIKE $s OR COALESCE(perc_al_g,'') ILIKE $s OR COALESCE(perc_al_a,'') ILIKE $s OR COALESCE(perc_al_r,'') ILIKE $s)";
	}

	$whereSql = '';
	if (!empty($where)) {
		$whereSql = "WHERE " . implode(" AND ", $where);
	}

	$baseQuery="SELECT 
				CONCAT(p.nome, ' (', COALESCE(REPLACE(p.note, 'LOCALITA', ''), ''), ')') AS nome,
				p.tipo,
				p.id::VARCHAR, 
				MAX(l.data_ora) AS last_update,
				p.perc_al_g,
				p.perc_al_a,
				p.perc_al_r,
				NULL AS arancio, 
				NULL AS rosso,
				NOW() AT TIME ZONE 'Europe/Rome' AS \"NOW\",
				MAX(CASE 
					WHEN l.data_ora >= (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '390 minutes') AND l.data_ora < (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '330 minutes') THEN l.id_lettura 
					ELSE NULL 
				END) AS \"6\",
				MAX(CASE 
					WHEN l.data_ora >= (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '330 minutes') AND l.data_ora < (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '270 minutes') THEN l.id_lettura 
					ELSE NULL 
				END) AS \"5\",
				MAX(CASE 
					WHEN l.data_ora >= (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '270 minutes') AND l.data_ora < (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '210 minutes') THEN l.id_lettura 
					ELSE NULL 
				END) AS \"4\",
				MAX(CASE 
					WHEN l.data_ora >= (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '210 minutes') AND l.data_ora < (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '150 minutes') THEN l.id_lettura 
					ELSE NULL 
				END) AS \"3\",
				MAX(CASE 
					WHEN l.data_ora >= (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '150 minutes') AND l.data_ora < (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '90 minutes') THEN l.id_lettura 
					ELSE NULL 
				END) AS \"2\",
				MAX(CASE 
					WHEN l.data_ora >= (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '90 minutes') AND l.data_ora < (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '30 minutes') THEN l.id_lettura 
					ELSE NULL 
				END) AS \"1\",
				MAX(CASE 
					WHEN l.data_ora >= (NOW() AT TIME ZONE 'Europe/Rome' - INTERVAL '30 minutes') AND l.data_ora < NOW() AT TIME ZONE 'Europe/Rome' THEN l.id_lettura 
					ELSE NULL 
				END) AS \"0\"
			FROM 
				geodb.punti_monitoraggio_ok p
			LEFT JOIN 
				geodb.lettura_mire l 
				ON l.num_id_mira = p.id 
			WHERE 
				p.id IS NOT NULL 
				AND (p.tipo ILIKE 'mira' OR p.tipo ILIKE 'rivo')
			GROUP BY 
				p.nome, p.id, p.note, p.tipo, p.perc_al_g, p.perc_al_a, p.perc_al_r

			UNION

			SELECT p.name AS nome,
				'IDROMETRO ARPA'::character varying AS tipo,
				l.id_station::text AS id,
				max(l.data_ora) as last_update,
				NULL as perc_al_g,
				NULL as perc_al_a,
				NULL as perc_al_r,
				s.liv_arancione as arancio,
				s.liv_rosso as rosso,
				NOW() AT TIME ZONE 'Europe/Rome' AS \"NOW\",
				( select greatest(max(lettura_idrometri_arpa.lettura),0) as max
					from geodb.lettura_idrometri_arpa
					where p.shortcode::text = lettura_idrometri_arpa.id_station::text 
						and lettura_idrometri_arpa.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '06:00:00'::interval) 
						and lettura_idrometri_arpa.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '05:00:00'::interval)
				) AS \"6\",
				( select greatest(max(lettura_idrometri_arpa.lettura),0) as max
					from geodb.lettura_idrometri_arpa
					where p.shortcode::text = lettura_idrometri_arpa.id_station::text 
						and lettura_idrometri_arpa.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '05:00:00'::interval) 
						and lettura_idrometri_arpa.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '04:00:00'::interval)
				) as \"5\",
				( select greatest(max(lettura_idrometri_arpa.lettura),0) as max
					from geodb.lettura_idrometri_arpa
					where p.shortcode::text = lettura_idrometri_arpa.id_station::text 
						and lettura_idrometri_arpa.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '04:00:00'::interval) 
						and lettura_idrometri_arpa.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '03:00:00'::interval)
				) AS \"4\",
				( select greatest(max(lettura_idrometri_arpa.lettura),0) as max
					from geodb.lettura_idrometri_arpa
					where p.shortcode::text = lettura_idrometri_arpa.id_station::text 
						and lettura_idrometri_arpa.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '03:00:00'::interval) 
						and lettura_idrometri_arpa.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '02:00:00'::interval)
				) AS \"3\",
				( select greatest(max(lettura_idrometri_arpa.lettura),0) as max
					from geodb.lettura_idrometri_arpa
					where p.shortcode::text = lettura_idrometri_arpa.id_station::text 
						and lettura_idrometri_arpa.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '02:00:00'::interval)
						and lettura_idrometri_arpa.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '01:00:00'::interval)
				) AS \"2\",
				( select greatest(max(lettura_idrometri_arpa.lettura),0) as max
					from geodb.lettura_idrometri_arpa
					where p.shortcode::text = lettura_idrometri_arpa.id_station::text
					and lettura_idrometri_arpa.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '01:00:00'::interval) 
					and lettura_idrometri_arpa.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '00:10:00'::interval)
				) AS \"1\",
				( select greatest(max(lettura_idrometri_arpa.lettura),0) as max
					from geodb.lettura_idrometri_arpa
					where p.shortcode::text = lettura_idrometri_arpa.id_station::text 
					and lettura_idrometri_arpa.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '00:25:00'::interval) 
					and lettura_idrometri_arpa.data_ora < timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome')
				) AS \"0\"
			FROM geodb.tipo_idrometri_arpa p
				LEFT JOIN geodb.lettura_idrometri_arpa l ON l.id_station::text = p.shortcode::text
				LEFT JOIN geodb.soglie_idrometri_arpa s ON p.shortcode::text = s.cod::text
			GROUP BY p.name, l.id_station, p.shortcode, s.liv_arancione, s.liv_rosso

  			UNION

			SELECT p.nome AS nome,
				'IDROMETRO COMUNE'::character varying AS tipo,
				l.id_station::text AS id,
				max(l.data_ora) as last_update,
				NULL as perc_al_g,
				NULL as perc_al_a,
				NULL as perc_al_r,
				s.liv_arancione as arancio,
				s.liv_rosso as rosso,
				NOW() AT TIME ZONE 'Europe/Rome' AS \"NOW\",
				( select greatest(max(lettura_idrometri_comune.lettura),0) as max
					from geodb.lettura_idrometri_comune
					where p.id::text = lettura_idrometri_comune.id_station::text 
						and lettura_idrometri_comune.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '06:00:00'::interval) 
						and lettura_idrometri_comune.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '05:00:00'::interval)
				) AS \"6\",
				( select greatest(max(lettura_idrometri_comune.lettura),0) as max
					from geodb.lettura_idrometri_comune
					where p.id::text = lettura_idrometri_comune.id_station::text 
						and lettura_idrometri_comune.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '05:00:00'::interval) 
						and lettura_idrometri_comune.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '04:00:00'::interval)
				) AS \"5\",
				( select greatest(max(lettura_idrometri_comune.lettura),0) as max
					from geodb.lettura_idrometri_comune
					where p.id::text = lettura_idrometri_comune.id_station::text 
						and lettura_idrometri_comune.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '04:00:00'::interval) 
						and lettura_idrometri_comune.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '03:00:00'::interval)
				) AS \"4\",
				( select greatest(max(lettura_idrometri_comune.lettura),0) as max
					from geodb.lettura_idrometri_comune
					where p.id::text = lettura_idrometri_comune.id_station::text 
						and lettura_idrometri_comune.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '03:00:00'::interval) 
						and lettura_idrometri_comune.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '02:00:00'::interval)
				) AS \"3\",
				( select greatest(max(lettura_idrometri_comune.lettura),0) as max
					from geodb.lettura_idrometri_comune
					where p.id::text = lettura_idrometri_comune.id_station::text 
						and lettura_idrometri_comune.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '02:00:00'::interval) 
						and lettura_idrometri_comune.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '01:00:00'::interval)
				) AS \"2\",
				( select greatest(max(lettura_idrometri_comune.lettura),0) as max
					from geodb.lettura_idrometri_comune
					where p.id::text = lettura_idrometri_comune.id_station::text 
						and lettura_idrometri_comune.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '01:00:00'::interval) 
						and lettura_idrometri_comune.data_ora < (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '00:10:00'::interval)
				) AS \"1\",
				( select greatest(max(lettura_idrometri_comune.lettura),0) as max
					from geodb.lettura_idrometri_comune
					where p.id::text = lettura_idrometri_comune.id_station::text 
						and lettura_idrometri_comune.data_ora >= (timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome') - '00:25:00'::interval) 
						and lettura_idrometri_comune.data_ora < timezone('Europe/Rome'::text, NOW() AT TIME ZONE 'Europe/Rome')
				) AS \"0\"
			FROM geodb.tipo_idrometri_comune p 
				LEFT JOIN geodb.lettura_idrometri_comune l ON l.id_station::text = p.id::text
				LEFT JOIN geodb.soglie_idrometri_comune s ON p.id::text = s.id::text
				WHERE p.usato = 't' and p.doppione_arpa = 'f'
			GROUP BY p.nome, l.id_station, p.id, s.liv_arancione, s.liv_rosso
			";

	// total count
	$queryTotal = "SELECT COUNT(*) AS total FROM ($baseQuery) q $whereSql;";
	$resultTotal = pg_query($conn, $queryTotal);
	$total = 0;
	if ($resultTotal) {
		$rTot = pg_fetch_assoc($resultTotal);
		if ($rTot && isset($rTot['total'])) {
			$total = (int)$rTot['total'];
		}
	}

	// rows
	$queryRows = "SELECT * FROM ($baseQuery) q $whereSql ORDER BY $sortSql $orderSql NULLS LAST LIMIT $limit OFFSET $offset;";

	$result = pg_query($conn, $queryRows);

	$rows = [];

	if ($result) {
		while ($row = pg_fetch_assoc($result)) {
			$rows[] = [
				"nome" => $row["nome"],
				"tipo" => $row["tipo"],
				"id" => $row["id"],
				"last_update" => $row["last_update"],
				"perc_al_g" => $row["perc_al_g"],
				"perc_al_a" => $row["perc_al_a"],
				"perc_al_r" => $row["perc_al_r"],
				"arancio" => $row["arancio"],
				"rosso" => $row["rosso"],
				"6" => !empty($row["6"]) ? $row["6"] : null,
				"5" => !empty($row["5"]) ? $row["5"] : null,
				"4" => !empty($row["4"]) ? $row["4"] : null,
				"3" => !empty($row["3"]) ? $row["3"] : null,
				"2" => !empty($row["2"]) ? $row["2"] : null,
				"1" => !empty($row["1"]) ? $row["1"] : null,
				"0" => !empty($row["0"]) ? $row["0"] : null,
				"NOW" => $row["NOW"],
			];
		}
	}

	pg_close($conn);

	header('Content-Type: application/json; charset=utf-8');
	echo json_encode([
		"total" => $total,
		"rows" => $rows
	], JSON_UNESCAPED_UNICODE);
}

?>
