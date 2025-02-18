<?php
while($r_s = pg_fetch_assoc($result_s)) {
	if ($r_s['data_ora_cambio']!=''){
		$data_cambio = $r_s['data_ora_cambio'];
	} else if ($r_s['time_stop'] !=' ') {
		$data_cambio = $r_s['time_stop'];
	} else {
		$data_cambio = date("Y-m-d H:i:s");
	}

	echo "<li>Dalle ore ".$r_s['data_ora']." alle ore ".$data_cambio." squadra <b>".$r_s['nome']." </b><ul>";
	$query_ss = "SELECT b.cognome, b.nome, a.capo_squadra FROM users.t_componenti_squadre a
		JOIN varie.dipendenti_storici b 
            ON a.matricola_cf = b.matricola  
		WHERE a.id_squadra = $1 
            AND (
                (a.data_start < $2 AND (a.data_end > $2 OR a.data_end IS NULL)
            ) OR (a.data_start < $3 AND (a.data_end > $3 OR a.data_end IS NULL)
            ))
		UNION 
        
        SELECT b.cognome, b.nome, a.capo_squadra 
        FROM users.t_componenti_squadre a
		JOIN users.utenti_esterni b 
            ON a.matricola_cf = b.cf 
		WHERE a.id_squadra = $4 AND ((a.data_start < $2 AND (a.data_end > $2 or a.data_end IS NULL)) OR
		    (a.data_start < $3 AND (a.data_end > $3 or a.data_end IS NULL)))
		UNION 
        
        SELECT b.cognome, b.nome, a.capo_squadra 
        FROM users.t_componenti_squadre a
		JOIN users.utenti_esterni_eliminati b 
            ON a.matricola_cf = b.cf 
		WHERE a.id_squadra = $1 AND ((a.data_start < $2 AND (a.data_end > $2 or a.data_end IS NULL)) OR
		    (a.data_start < $3 AND (a.data_end > $3 or a.data_end IS NULL)))
		UNION 
        
        SELECT sqd.cognome, sqd.nome, 'f' AS capo_squadra
		FROM users.v_personale_squadre2 sqd
		JOIN users.t_componenti_squadre a 
            ON a.matricola_cf = sqd.matricola_cf 
		WHERE sqd.id_squadra::numeric = $1 AND ((a.data_start < $2 AND (a.data_end > $2 or a.data_end IS NULL)) OR
		    (a.data_start < $3 AND (a.data_end > $3 or a.data_end IS NULL)))
		ORDER BY cognome;";


        $params = [$r_s['id_squadra'], $r_s['data_ora'], $data_cambio, $r_s['id_squadra'] ];
        $result_ss = pg_query_params($conn, $query_ss, $params);
		// $result_ss = pg_query($conn, $query_ss);

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
