<?php
session_start();
//require('../validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$profilo = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT);
$livello = pg_escape_string($_GET['l']);
// $boll_pc = filter_input(INPUT_GET, 'boll_pc', FILTER_VALIDATE_INT) ?? 0;

// if ($profilo === 3){
// 	$filter = ' ';
// } elseif ($profilo === 8){
// 	$filter= ' WHERE id_profilo=\''.$profilo.'\' and nome_munic = \''.$livello.'\' ';
// } else {
// 	$filter= ' WHERE id_profilo=\''.$profilo.'\' ';
// }


if (!$conn) {
    echo json_encode(['error' => 'Connection failed!']);
    exit;
}


$query="SELECT DISTINCT ON (u.telegram_id) u.matricola_cf,
							u.nome,
							u.cognome,
							jtfc.funzione,
							u.telegram_id,
							tp.data_invio,
							tp.lettura,
							tp.data_conferma,
							tp.data_invio_conv,
							tp.data_conferma_conv,
							tp.lettura_conv,
							tp.id_bollettino
		FROM users.utenti_coc u
		JOIN users.t_convocazione tp 
			ON u.telegram_id::text = tp.id_telegram::text
		JOIN users.tipo_funzione_coc jtfc 
			ON jtfc.id = u.funzione
		LEFT JOIN eventi.t_bollettini b
			ON b.id = tp.id_bollettino
		WHERE  tp.data_invio_conv IS NOT null
		ORDER BY u.telegram_id, tp.data_invio_conv DESC NULLS LAST, tp.data_invio DESC;";

$result = pg_prepare($conn, "myquery0", $query);
$result = pg_execute($conn, "myquery0", array());

// Check for query execution errors
if (!$result) {
    echo json_encode(['error' => 'Query failed!']);
    pg_close($conn);
    exit;
}
$rows = pg_fetch_all($result);

pg_close($conn);

// Output result as JSON
if ($rows){
	echo json_encode($rows);
} else {
	echo json_encode([['NOTE' => 'No data']]);
}

?>


