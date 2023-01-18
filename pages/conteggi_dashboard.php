<?php

// conteggi per pannelli notifiche della dashboard

//****************************************
// CONTEGGI
//****************************************
// segnalazioni totali
// echo 'This is the conteggi_dashboard.php first fetch<br>';
$query = "SELECT count(id) FROM segnalazioni.v_segnalazioni;";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$segn_tot = $r['count'];
}

// segnalazioni in lavorazione
// echo 'This is the conteggi_dashboard.php first fetch<br>';
$query = "SELECT count(id) FROM segnalazioni.v_segnalazioni WHERE in_lavorazione='t';";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$segn_lav = $r['count'];
}

// segnalazioni chiuse
// echo 'This is the conteggi_dashboard.php first fetch<br>';
$query = "SELECT count(id) FROM segnalazioni.v_segnalazioni WHERE in_lavorazione='f';";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$segn_chiuse = $r['count'];
}

// squadre PC
// echo 'This is the conteggi_dashboard.php first fetch<br>';
$query = "SELECT count(id) FROM users.v_squadre WHERE id_stato=1 AND cod_afferenza='com_PC';";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$squadre_in_azione = $r['count'];
}

// echo 'This is the conteggi_dashboard.php first fetch<br>';
$query = "SELECT count(id) FROM users.v_squadre WHERE id_stato=2 AND cod_afferenza='com_PC';";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$squadre_disposizione = $r['count'];
}



// Conteggi provvedimenti cautelari
// echo 'This is the conteggi_dashboard.php first fetch<br>';
$query = "SELECT count(id) FROM segnalazioni.v_provvedimenti_cautelari_last_update where id_stato_provvedimenti_cautelari=1;";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$pc_assegnati = $r['count'];
}


// echo 'This is the conteggi_dashboard.php first fetch<br>';
$query = "SELECT count(id) FROM segnalazioni.v_provvedimenti_cautelari_last_update where id_stato_provvedimenti_cautelari=2;";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$pc_corso = $r['count'];
}


// echo 'This is the conteggi_dashboard.php LAST fetch<br>';
$query = "SELECT count(id) FROM segnalazioni.v_provvedimenti_cautelari_last_update where id_stato_provvedimenti_cautelari=3;";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
	$pc_conclusi = $r['count'];
}




?>