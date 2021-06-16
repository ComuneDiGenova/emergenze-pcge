<?php
session_start();
//require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
$profilo=pg_escape_string($_GET['p']);


//cerco il codice afferenza perchè le query successive sono molto più rapide
$query="SELECT cod FROM varie.v_incarichi_mail where profilo='".$profilo."';";
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$cod_profilo_squadra=$r["cod"];
}


$tipo=(int)pg_escape_string($_GET['t']);

if ($tipo==1){
	//squadre attive
	$filter= ' and da_nascondere=\'f\' and num_componenti > 0 and id_stato < 3 ';
} else if($tipo==0){
	//squadre non attive
	$filter= ' and da_nascondere=\'f\' and (num_componenti = 0 or id_stato = 3) ';
} else if($tipo==2){
	//squadre non attive
	$filter= ' and da_nascondere=\'t\'';
}

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
	//$idcivico=$_GET["id"];
	/* $query="SELECT s.id,s.nome,s.stato,s.id_stato,s.num_componenti,s.componenti, s.da_nascondere, i.descrizione, i.id as id_incarico 
	FROM users.v_squadre s
	LEFT JOIN segnalazioni.v_incarichi_squadre i ON s.id::integer=i.id_squadra::integer 
	WHERE s.cod_afferenza='".$cod_profilo_squadra."' ".$filter." 
	ORDER BY nome ;"; */
	$query="SELECT s.id,s.nome,s.stato,s.id_stato,s.num_componenti,s.componenti, s.da_nascondere, i.descrizione, i.id as id_incarico, capo_assegnato.capo_squadra, capo_presente.operativo
	FROM users.v_squadre s
	LEFT JOIN segnalazioni.v_incarichi_squadre i ON s.id::integer=i.id_squadra::integer
	LEFT JOIN (select vcs.id, vcs.capo_squadra, vcs.matricola_cf from users.v_componenti_squadre vcs 
	where capo_squadra=true
	group by vcs.id, vcs.capo_squadra, vcs.matricola_cf) as capo_assegnato on capo_assegnato.id::integer = s.id::integer
	LEFT JOIN (select vup.id, vup.operativo, vup.matricola_cf from users.v_utenti_presenti vup 
	group by vup.id, vup.operativo, vup.matricola_cf) as capo_presente on capo_presente.matricola_cf = capo_assegnato.matricola_cf
	WHERE s.cod_afferenza='".$cod_profilo_squadra."' ".$filter." 
	ORDER BY nome ;";
   //echo $query;
	$result = pg_query($conn, $query);
	//echo $query;
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


