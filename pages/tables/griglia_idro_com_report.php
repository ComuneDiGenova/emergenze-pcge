<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
// 	$query="SELECT concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome,
// 	p.tipo,
// 	p.id::character varying, 
// 	max(l.data_ora) as last_update,
// 	p.perc_al_g as perc_al_g,
// 	p.perc_al_a as perc_al_a,
// 	p.perc_al_r as perc_al_r,
// 	NULL as arancio, 
// 	NULL as rosso,
// 	(select max(id_lettura) 
// 	 from geodb.lettura_mire  
// 	 where num_id_mira = p.id and 
// 	 data_ora > (now()- interval '6 hour') and data_ora < (now()- interval '5 hour') 
// 	) as \"6\",
// 	(select max(id_lettura) 
// 	 from geodb.lettura_mire  
// 	 where num_id_mira = p.id and 
// 	 data_ora > (now()- interval '5 hour') and data_ora < (now()- interval '4 hour') 
// 	) as \"5\",
// 	(select max(id_lettura) 
// 	 from geodb.lettura_mire  
// 	 where num_id_mira = p.id and 
// 	 data_ora > (now()- interval '4 hour') and data_ora < (now()- interval '3 hour') 
// 	) as \"4\",
// 	(select max(id_lettura) 
// 	 from geodb.lettura_mire  
// 	 where num_id_mira = p.id and 
// 	 data_ora > (now()- interval '3 hour') and data_ora < (now()- interval '2 hour') 
// 	) as \"3\",
// 	(select max(id_lettura) 
// 	 from geodb.lettura_mire  
// 	 where num_id_mira = p.id and 
// 	 data_ora > (now()- interval '2 hour') and data_ora < (now()- interval '1 hour') 
// 	) as \"2\",
// 	(select max(id_lettura) 
// 	 from geodb.lettura_mire  
// 	 where num_id_mira = p.id and 
// 	 data_ora > (now()- interval '1 hour') and data_ora < (now()- interval '10 minutes') 
// 	) as \"1\",
// 	(select max(id_lettura) 
// 	 from geodb.lettura_mire  
// 	 where num_id_mira = p.id and 
// 	 data_ora > (now()- interval '10 minutes') and data_ora < now() 
// 	) as \"0\"
// 	FROM geodb.punti_monitoraggio_ok p
// 	LEFT JOIN geodb.lettura_mire l ON l.num_id_mira = p.id 
// 	WHERE p.tipo ilike 'mira' OR p.tipo ilike 'rivo' and p.id is not null 
// 	group by p.nome, p.id, p.note, p.tipo, p.perc_al_g, p.perc_al_a, p.perc_al_r
// 	order by p.nome;
//    UNION
//    $query="SELECT p.name AS nome,
//     'IDROMETRO ARPA'::character varying AS tipo,
//     l.id_station::text AS id,
//     max(l.data_ora) AT TIME ZONE 'UTC' AT TIME ZONE 'CEST' AS last_update,
//     NULL as perc_al_g,
// 	NULL as perc_al_a,
// 	NULL as perc_al_r,
// 	s.liv_arancione as arancio,
//     s.liv_rosso as rosso,
//     ( SELECT greatest(max(lettura_idrometri_arpa.lettura),0) AS max
//            FROM geodb.lettura_idrometri_arpa
//           WHERE p.shortcode::text = lettura_idrometri_arpa.id_station::text AND lettura_idrometri_arpa.data_ora > (timezone('utc'::text, now()) - '06:00:00'::interval) AND lettura_idrometri_arpa.data_ora < (timezone('utc'::text, now()) - '05:00:00'::interval)) AS \"6\",
//     ( SELECT greatest(max(lettura_idrometri_arpa.lettura),0) AS max
//            FROM geodb.lettura_idrometri_arpa
//           WHERE p.shortcode::text = lettura_idrometri_arpa.id_station::text AND lettura_idrometri_arpa.data_ora > (timezone('utc'::text, now()) - '05:00:00'::interval) AND lettura_idrometri_arpa.data_ora < (timezone('utc'::text, now()) - '04:00:00'::interval)) AS \"5\",
//     ( SELECT greatest(max(lettura_idrometri_arpa.lettura),0) AS max
//            FROM geodb.lettura_idrometri_arpa
//           WHERE p.shortcode::text = lettura_idrometri_arpa.id_station::text AND lettura_idrometri_arpa.data_ora > (timezone('utc'::text, now()) - '04:00:00'::interval) AND lettura_idrometri_arpa.data_ora < (timezone('utc'::text, now()) - '03:00:00'::interval)) AS \"4\",
//     ( SELECT greatest(max(lettura_idrometri_arpa.lettura),0) AS max
//            FROM geodb.lettura_idrometri_arpa
//           WHERE p.shortcode::text = lettura_idrometri_arpa.id_station::text AND lettura_idrometri_arpa.data_ora > (timezone('utc'::text, now()) - '03:00:00'::interval) AND lettura_idrometri_arpa.data_ora < (timezone('utc'::text, now()) - '02:00:00'::interval)) AS \"3\",
//     ( SELECT greatest(max(lettura_idrometri_arpa.lettura),0) AS max
//            FROM geodb.lettura_idrometri_arpa
//           WHERE p.shortcode::text = lettura_idrometri_arpa.id_station::text AND lettura_idrometri_arpa.data_ora > (timezone('utc'::text, now()) - '02:00:00'::interval) AND lettura_idrometri_arpa.data_ora < (timezone('utc'::text, now()) - '01:00:00'::interval)) AS \"2\",
//     ( SELECT greatest(max(lettura_idrometri_arpa.lettura),0) AS max
//            FROM geodb.lettura_idrometri_arpa
//           WHERE p.shortcode::text = lettura_idrometri_arpa.id_station::text AND lettura_idrometri_arpa.data_ora > (timezone('utc'::text, now()) - '01:00:00'::interval) AND lettura_idrometri_arpa.data_ora < (timezone('utc'::text, now()) - '00:10:00'::interval)) AS \"1\",
//     ( SELECT greatest(max(lettura_idrometri_arpa.lettura),0) AS max
//            FROM geodb.lettura_idrometri_arpa
//           WHERE p.shortcode::text = lettura_idrometri_arpa.id_station::text AND lettura_idrometri_arpa.data_ora > (timezone('utc'::text, now()) - '00:25:00'::interval) AND lettura_idrometri_arpa.data_ora < timezone('utc'::text, now())) AS \"0\"
//    FROM geodb.tipo_idrometri_arpa p
//      LEFT JOIN geodb.lettura_idrometri_arpa l ON l.id_station::text = p.shortcode::text
//      LEFT JOIN geodb.soglie_idrometri_arpa s ON p.shortcode::text = s.cod::text
//   GROUP BY p.name, l.id_station, p.shortcode, s.liv_arancione, s.liv_rosso
//   UNION 
  $query="SELECT p.nome AS nome,
  'IDROMETRO COMUNE'::character varying AS tipo,
  l.id_station::text AS id,
  max(l.data_ora) AT TIME ZONE 'UTC' AT TIME ZONE 'CEST' AS last_update,
      NULL as perc_al_g,
     NULL as perc_al_a,
     NULL as perc_al_r,
     s.liv_arancione as arancio,
  s.liv_rosso as rosso,
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '08:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '07:30:00'::interval)) AS \"16\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '07:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '07:00:00'::interval)) AS \"15\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '07:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '06:30:00'::interval)) AS \"14\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '06:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '06:00:00'::interval)) AS \"13\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '06:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '05:30:00'::interval)) AS \"12\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '05:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '05:00:00'::interval)) AS \"11\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '05:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '04:30:00'::interval)) AS \"10\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '04:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '04:00:00'::interval)) AS \"9\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '04:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '03:30:00'::interval)) AS \"8\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '03:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '03:00:00'::interval)) AS \"7\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '03:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '02:30:00'::interval)) AS \"6\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '02:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '02:00:00'::interval)) AS \"5\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '02:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '01:30:00'::interval)) AS \"4\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '01:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '01:00:00'::interval)) AS \"3\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '01:00:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()) - '00:30:00'::interval)) AS \"2\",
  ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
         FROM geodb.lettura_idrometri_comune
        WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '00:30:00'::interval) AND lettura_idrometri_comune.data_ora < (timezone('utc'::text, now()))) AS \"1\",
   ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
          FROM geodb.lettura_idrometri_comune
         WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= (timezone('utc'::text, now()) - '00:25:00'::interval) AND lettura_idrometri_comune.data_ora < timezone('utc'::text, now())) AS \"0\",
         ( SELECT greatest(max(lettura_idrometri_comune.lettura),0) AS max
          FROM geodb.lettura_idrometri_comune
     WHERE p.id::text = lettura_idrometri_comune.id_station::text AND lettura_idrometri_comune.data_ora >= timezone('utc'::text, now())) AS \"00\"
        FROM geodb.tipo_idrometri_comune p 
   LEFT JOIN geodb.lettura_idrometri_comune l ON l.id_station::text = p.id::text
   LEFT JOIN geodb.soglie_idrometri_comune s ON p.id::text = s.id::text
   WHERE p.usato = 't' and p.doppione_arpa = 'f'
GROUP BY p.nome, l.id_station, p.id, s.liv_arancione, s.liv_rosso
 order by nome;";
   //echo $query;
	$result = pg_query($conn, $query);
	//echo $query;
	//exit;
	$rows = array();
	while($r = pg_fetch_assoc($result)) {
    		$rows[] = $r;
    		//$rows[] = $rows[]. "<a href='puntimodifica.php?id=" . $r["NAME"] . "'>edit <img src='../../famfamfam_silk_icons_v013/icons/database_edit.png' width='16' height='16' alt='' /> </a>";
	}
	pg_close($conn);
	#echo $rows ;
	if (empty($rows)==FALSE){
		//print $rows;
		print json_encode(array_values(pg_fetch_all($result)));
	} else {
		echo "[{\"NOTE\":'No data'}]";
	}
}

?>


