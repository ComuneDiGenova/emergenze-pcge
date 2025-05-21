<?php
session_start();

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$profilo = filter_input(INPUT_GET, 'p', FILTER_VALIDATE_INT);
$livello = pg_escape_string($_GET['l']);


if (!$conn) {
    echo json_encode(['error' => 'Connection failed!']);
    exit;
}

$query="SELECT DISTINCT ON (u.telegram_id) 
			u.matricola_cf,
			u.nome,
			u.cognome,
			jtfc.funzione,
			TO_CHAR(tlb.data_invio, 'YYYY-MM-DD HH24:MI:SS') AS data_invio,
			tlb.lettura,
			TO_CHAR(tlb.data_conferma, 'YYYY-MM-DD HH24:MI:SS') AS data_conferma,
			TO_CHAR(tlcc.data_invio_conv, 'YYYY-MM-DD HH24:MI:SS') AS data_invio_conv,
			TO_CHAR(tlcc.data_conferma_conv, 'YYYY-MM-DD HH24:MI:SS') AS data_conferma_conv,
			tlcc.lettura_conv
		FROM users.utenti_coc u
		LEFT JOIN users.t_lettura_conv_coc tlcc 
			ON u.telegram_id::text = tlcc.id_telegram::text
		LEFT JOIN users.t_lettura_bollettino tlb
			ON u.telegram_id::text = tlb.id_telegram::text
			AND tlb.id_convocazione = tlcc.id_convocazione  
		JOIN users.tipo_funzione_coc jtfc 
			ON jtfc.id = u.funzione
		ORDER BY u.telegram_id, tlcc.data_invio_conv DESC NULLS LAST, tlb.data_invio DESC NULLS LAST;";

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


