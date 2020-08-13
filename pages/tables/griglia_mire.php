<?php
session_start();
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	$query="SELECT concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome, p.tipo, p.id, 
	max(l.data_ora) as last_update,
	(select id_lettura 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > (now()- interval '6 hour') and data_ora < (now()- interval '5 hour') 
	) as \"6\",
	(select id_lettura 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > (now()- interval '5 hour') and data_ora < (now()- interval '4 hour') 
	) as \"5\",
	(select id_lettura 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > (now()- interval '4 hour') and data_ora < (now()- interval '3 hour') 
	) as \"4\",
	(select id_lettura 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > (now()- interval '3 hour') and data_ora < (now()- interval '2 hour') 
	) as \"3\",
	(select id_lettura 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > (now()- interval '2 hour') and data_ora < (now()- interval '1 hour') 
	) as \"2\",
	(select id_lettura 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > (now()- interval '1 hour') and data_ora < (now()- interval '10 minutes') 
	) as \"1\",
	(select id_lettura 
	 from geodb.lettura_mire  
	 where num_id_mira = p.id and 
	 data_ora > (now()- interval '10 minutes') and data_ora < now() 
	) as \"0\"
	FROM geodb.punti_monitoraggio_ok p
	LEFT JOIN geodb.lettura_mire l ON l.num_id_mira = p.id
	WHERE p.tipo ilike 'mira' OR p.tipo ilike 'rivo' 
	group by p.nome, p.id, p.note, p.tipo
	order by nome;";
    
    //echo $query;
	$result = pg_query($conn, $query);
	#echo $query;
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


