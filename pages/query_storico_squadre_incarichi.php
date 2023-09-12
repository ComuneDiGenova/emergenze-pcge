<?php
//echo "<ul>";
$result_s=pg_query($conn, $query_s);
while($r_s = pg_fetch_assoc($result_s)) {
	if ($r_s['data_ora_cambio']!=''){
		$data_cambio=$r_s['data_ora_cambio'];
	} else if ($r_s['time_stop']!='') {
		$data_cambio=$r_s['time_stop'];
	} else {
		$data_cambio=date("Y-m-d H:i:s");
	}
	echo "<li>Dalle ore ".$r_s['data_ora']." alle ore ".$data_cambio." squadra <b>".$r_s['nome']." </b><ul>";
	$query_ss="SELECT b.cognome, b.nome, a.capo_squadra FROM users.t_componenti_squadre a
		JOIN varie.dipendenti_storici b ON a.matricola_cf = b.matricola  
		WHERE a.id_squadra = ".$r_s['id_squadra']. " and 
		((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
		(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
		UNION SELECT b.cognome, b.nome, a.capo_squadra FROM users.t_componenti_squadre a
		JOIN users.utenti_esterni b ON a.matricola_cf = b.cf 
		WHERE a.id_squadra = ".$r_s['id_squadra']. " and 
		((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
		(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
		UNION SELECT b.cognome, b.nome, a.capo_squadra FROM users.t_componenti_squadre a
		JOIN users.utenti_esterni_eliminati b ON a.matricola_cf = b.cf 
		WHERE a.id_squadra = ".$r_s['id_squadra']. "  and 
		((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
		(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
		UNION SELECT sqd.cognome, sqd.nome, 'f' as capo_squadra
		from users.v_personale_squadre2 sqd
		where sqd.id_squadra::numeric = ".$r_s['id_squadra']. "
		ORDER BY cognome";
		// echo $query_ss;
		$result_ss=pg_query($conn, $query_ss);
		while($r_ss = pg_fetch_assoc($result_ss)) {
			echo "<li>".$r_ss['cognome']." ".$r_ss['nome']." ";
			if ($r_ss['capo_squadra']=='t'){
				echo '(<i class="fas fa-user-tie" title="Capo squadra"></i>)';
			}
			echo "</li>";
		}
	
	echo "</ul></li>";
}
?>
