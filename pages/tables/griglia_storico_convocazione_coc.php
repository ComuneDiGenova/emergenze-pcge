<?php
session_start();
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

// Esegui la query
$query = "SELECT 
			jtfc.funzione AS funzione,
			u.cognome AS cognome,
			u.nome AS nome,
			tlb.data_invio::timestamp::date AS data_invio,
			tlb.data_invio::timestamp::time AS ora_invio,
			CASE 
				WHEN tlb.lettura IS TRUE THEN 't' 
				ELSE '' 
			END AS lettura,
			tlb.data_conferma AS data_conferma,
			tlcc.data_invio_conv::timestamp::date AS data_invio_conv,
			tlcc.data_invio_conv::timestamp::time AS ora_convocazione,
			CASE 
				WHEN tlcc.lettura_conv IS TRUE THEN 't' 
				ELSE '' 
			END AS lettura_conv,
			tlcc.data_conferma_conv AS data_conferma_conv
		FROM 
			users.utenti_coc u
		JOIN 
			users.tipo_funzione_coc jtfc 
			ON jtfc.id = u.funzione
		RIGHT JOIN 
			users.t_lettura_conv_coc tlcc
			ON u.telegram_id::text = tlcc.id_telegram::text
		LEFT JOIN 
			users.t_lettura_bollettino tlb
			ON u.telegram_id::text = tlb.id_telegram::text
			AND tlcc.id_bollettino = tlb.id_bollettino
		WHERE 
            tlcc.data_invio_conv >= NOW() - INTERVAL '1 month'
		GROUP BY 
			jtfc.funzione, u.cognome, u.nome, tlb.data_invio, tlb.lettura, tlb.data_conferma,
			tlcc.data_invio_conv, tlcc.lettura_conv, tlcc.data_conferma_conv
		ORDER BY 
			tlcc.data_invio_conv, tlb.data_invio, u.cognome, u.nome;";

$result = pg_query($conn, $query);

$data = [];

// Processa i risultati della query e inseriscili in un array
while ($row = pg_fetch_assoc($result)) {
    $data[] = [
        'funzione' => $row['funzione'],
        'cognome' => $row['cognome'],
        'nome' => $row['nome'],
        'data_invio' => $row['data_invio'],
        'ora_invio' => $row['ora_invio'],
        'lettura' => $row['lettura'],
        'data_conferma' => $row['data_conferma'],
        'data_invio_conv' => $row['data_invio_conv'],
        'ora_convocazione' => $row['ora_convocazione'],
        'lettura_conv' => $row['lettura_conv'],
        'data_conferma_conv' => $row['data_conferma_conv']
    ];
}

// Imposta l'intestazione per il JSON e restituisci i dati
header('Content-Type: application/json');
echo json_encode($data);
