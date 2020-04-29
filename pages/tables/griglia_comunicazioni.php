<?php
session_start();
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

//require('../check_evento.php');
$filtro_from='';
$filtro_to='';

// Filtro per tipologia di criticità
$getfiltri=$_GET["f"];
$filtro_evento_attivo=$_GET["a"];
$filtro_municipio=$_GET["m"];
$filtro_from=$_GET["from"];
$filtro_to=$_GET["to"];
$resp=$_GET["r"];

//echo $getfiltri;

if ($filtro_from!='' or $filtro_to!=''){
	$filtro= ' WHERE ';
}
if ($filtro_from!=''){
	$filtro= $filtro." data_ora_stato > ".$filtro_from."";;
}
if ($filtro_from!='' and $filtro_to!=''){
	$filtro= $filtro.' AND ';
}
if ($filtro_to!=''){
	$filtro= $filtro." data_ora_stato < ".$filtro_to."";;
}

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	//$query="SELECT id, id as id2, data_ora, descrizione, criticita, 
    //   rischio, id_evento, tipo_evento, 
    //   note, id_lavorazione, in_lavorazione, localizzazione, nome_munic, st_x(geom) as lon, st_y(geom) as lat FROM segnalazioni.v_segnalazioni_lista ".$filter[0]." ".$filter_r.";";
    
	$query= "select 
		'incarico' as tipo,
		id_incarico as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_incarichi
		".$filtro." UNION
		select 
		'incarico' as tipo,
		id_incarico as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_incarichi_inviate
		".$filtro." UNION
		select 
		'incarico_interno' as tipo,
		id_incarico as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_incarichi_interni
		".$filtro." UNION
		select 
		'incarico_interno' as tipo,
		id_incarico as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_incarichi_interni_inviate
		".$filtro." UNION
		select 
		'provvedimento_cautelare' as tipo,
		id_provvedimento as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_provvedimenti_cautelari
		".$filtro." UNION
		select 
		'provvedimento_cautelare' as tipo,
		id_provvedimento as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_provvedimenti_cautelari_inviate
		".$filtro." UNION
		select 
		'sopralluogo' as tipo,
		id_sopralluogo as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_sopralluoghi
		".$filtro." UNION
		select 
		'sopralluogo' as tipo,
		id_sopralluogo as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_sopralluoghi_inviate
		".$filtro." UNION
		select 
		'sopralluogo_mobile' as tipo,
		id_sopralluogo as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_sopralluoghi_mobili
		".$filtro." UNION
		select 
		'sopralluogo_mobile' as tipo,
		id_sopralluogo as id,
		testo,
		to_char(data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_sopralluoghi_mobili_inviate
		".$filtro." UNION
		select 
		'segnalazione' as tipo,
		s.id as id,
		c.testo,
		to_char(c.data_ora_stato, 'YYYY/MM/DD HH24:MI'::text) as data_ora_stato,
		case 
		when c.allegato is not null then 'y' end
		as allegato
		from segnalazioni.t_comunicazioni_segnalazioni c
		JOIN 
		(SELECT min(id_segnalazione) as id, id_segnalazione_in_lavorazione FROM segnalazioni.join_segnalazioni_in_lavorazione GROUP BY id_segnalazione_in_lavorazione) 
		as s ON s.id_segnalazione_in_lavorazione=c.id_lavorazione
		 ".$filtro." ORDER BY data_ora_stato desc;";
	
	
    //echo $query."<br>";
	$result = pg_query($conn, $query);
	#echo $query;
	#exit;
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


