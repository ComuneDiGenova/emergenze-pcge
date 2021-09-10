<?php
session_start();
require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome,
	p.tipo,
	p.id::character varying, 
	max(l.data_ora) as last_update,
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1440 minutes') and data_ora < roundToQuarterHour(now()- interval '1380 minutes') 
	) as \"24\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1380 minutes') and data_ora < roundToQuarterHour(now()- interval '1320 minutes') 
	) as \"23\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1320 minutes') and data_ora < roundToQuarterHour(now()- interval '1260 minutes') 
	) as \"22\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1260 minutes') and data_ora < roundToQuarterHour(now()- interval '1200 minutes') 
	) as \"21\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1200 minutes') and data_ora < roundToQuarterHour(now()- interval '1140 minutes') 
	) as \"20\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1140 minutes') and data_ora < roundToQuarterHour(now()- interval '1080 minutes') 
	) as \"19\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1080 minutes') and data_ora < roundToQuarterHour(now()- interval '1020 minutes') 
	) as \"18\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '1020 minutes') and data_ora < roundToQuarterHour(now()- interval '960 minutes') 
	) as \"17\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '960 minutes') and data_ora < roundToQuarterHour(now()- interval '900 minutes') 
	) as \"16\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '900 minutes') and data_ora < roundToQuarterHour(now()- interval '840 minutes') 
	) as \"15\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '840 minutes') and data_ora < roundToQuarterHour(now()- interval '780 minutes') 
	) as \"14\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '780 minutes') and data_ora < roundToQuarterHour(now()- interval '720 minutes') 
	) as \"13\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '720 minutes') and data_ora < roundToQuarterHour(now()- interval '660 minutes') 
	) as \"12\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '660 minutes') and data_ora < roundToQuarterHour(now()- interval '600 minutes') 
	) as \"11\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '600 minutes') and data_ora < roundToQuarterHour(now()- interval '540 minutes') 
	) as \"10\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '540 minutes') and data_ora < roundToQuarterHour(now()- interval '480 minutes') 
	) as \"9\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > roundToQuarterHour(now()- interval '480 minutes') and data_ora < roundToQuarterHour(now()- interval '420 minutes') 
	) as \"8\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '420 minutes') and data_ora < roundToQuarterHour(now()- interval '360 minutes')
	) as \"7\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '360 minutes') and data_ora < roundToQuarterHour(now()- interval '300 minutes') 
	) as \"6\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '300 minutes') and data_ora < roundToQuarterHour(now()- interval '240 minutes') 
	) as \"5\",
	(select max(id_lettura) 
	 from geodb.lettura_mire
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '240 minutes') and data_ora < roundToQuarterHour(now()- interval '180 minutes') 
	) as \"4\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '180 minutes') and data_ora < roundToQuarterHour(now()- interval '120 minutes') 
	) as \"3\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()- interval '120 minutes') and data_ora < roundToQuarterHour(now()- interval '60 minutes') 
	) as \"2\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 --data_ora >= roundToQuarterHour(now()- interval '30 minutes') and data_ora < roundToQuarterHour(now()- interval '10 minutes')
	 data_ora >= roundToQuarterHour(now()- interval '60 minutes') and data_ora < roundToQuarterHour(now()) 
	) as \"1\",
	-- (select max(id_lettura) 
	--  from geodb.lettura_mire  
	--  where num_id_mira = p.id and 
	--  data_ora >= roundToQuarterHour(now() - interval '10 minutes') and data_ora < now()
	-- ) as \"0\",
	(select max(id_lettura) 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora >= roundToQuarterHour(now()) 
	) as \"0\"
	FROM geodb.punti_monitoraggio_ok p
	LEFT JOIN geodb.lettura_mire l ON l.num_id_mira = p.id 
	WHERE p.tipo ilike 'mira' OR p.tipo ilike 'rivo' and p.id is not null 
	group by p.nome, p.id, p.note, p.tipo, p.perc_al_g, p.perc_al_a, p.perc_al_r
	order by p.nome;";
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


