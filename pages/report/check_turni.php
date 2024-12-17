
<?php
$check_turni=0;

$tabelle_turni = [
    't_coordinamento',
    't_monitoraggio_meteo',
    't_operatore_anpas',
    't_operatore_nverde',
    't_operatore_volontari',
    't_presidio_territoriale',
    't_tecnico_pc'
];

foreach ($tabelle_turni as $table) {

	$condizione="matricola_cf='".$cf."') and 
	(
	(data_start < '".$fine_data."' and data_start > '".$inizio_data."') OR
	(data_end < '".$fine_data."' and data_end > '".$inizio_data."') OR
	(data_start < '".$inizio_data."' and data_end > '".$fine_data."')";

	$query= "SELECT matricola_cf
            FROM report.".$table."
            WHERE (".$condizione.");";

	$result = pg_query($conn, $query);

	echo "<br>";
	echo $query;
    exit;
	while($r = pg_fetch_assoc($result)) {
		$check_turni=1;
		echo "Sono dentro<br>";
		$query2="update report.".$table." SET warning_turno='t' where (".$condizione.");";
		echo $query2;
		$result2 = pg_query($conn, $query2);
	}
}

echo "<br>Check_turni=".$check_turni."<br>";
if($check_turni==1){
	$wt='t';
} else {
	$wt='f';
}
//exit;
?>