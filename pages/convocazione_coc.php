<?php

session_start();
require('./validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$testo=str_replace("'", "''", $_POST['testoCoC']);
$boll_pc = $_POST['boll_pc'];

require('./token_telegram.php');
require('./send_message_telegram.php');

// Query per ottenere gli id telegram
$query_tg_ids = "SELECT DISTINCT ON (u.telegram_id) u.telegram_id
					FROM users.utenti_coc u;";

// Preparazione ed esecuzione della query per ottenere gli id telegram
$result = pg_prepare($conn, "get_tg_ids", $query_tg_ids);
$result = pg_execute($conn, "get_tg_ids", array());

// Estrai tutti gli id telegram come array
$tg_ids = pg_fetch_all($result);

$values = [];
foreach ($tg_ids as $row) {
	$chat_id_coc = $row['telegram_id'];

	if ($boll_pc == 0) {
        // Creo valori nel caso NON ci sia bollettono
        $values[] = "(
            date_trunc('hour', now()) + date_part('minute', now())::int / 10 * interval '10 min',
            '$chat_id_coc'
        )";
    } 
}

// Unisco tutti i valori in una singola stringa separata da virgole
$values_string = implode(", ", $values);

// Se non è presente nessun bollettino associato, devo creare una convocazione ad hoc per ogni utente coc
// (nel caso di chiamata con bollettino_PC la convocazione viene creata da readxml.py in automatico)
if ($boll_pc == 0) {
	// Query senza bollettino
	$query = "INSERT INTO users.t_convocazione (data_invio_conv, id_telegram) VALUES $values_string;";
} 

// Eseguo la query
$result = pg_query($conn, $query);


$query_render="SELECT DISTINCT ON (u.telegram_id) 
							u.matricola_cf,
							u.nome,
							u.cognome,
							jtfc.funzione,
							u.telegram_id,
							tp.id,
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
				WHERE ($boll_pc = 0 OR tp.id_bollettino = $boll_pc)
				ORDER BY u.telegram_id, tp.data_invio DESC, tp.data_invio_conv DESC;";


$result = pg_prepare($conn, "myquery", $query_render);
$result = pg_execute($conn, "myquery", array());

while($r = pg_fetch_assoc($result)) {
	if($r['data_invio_conv'] == null){
		$query0="UPDATE users.t_convocazione tc
				SET data_invio_conv = date_trunc('hour', now()) + date_part('minute', now())::int / 10 * interval '10 min'
				FROM (SELECT DISTINCT ON (u.telegram_id) u.matricola_cf,
									u.nome,
									u.cognome,
									jtfc.funzione,
									u.telegram_id,
									tp.id,
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
						WHERE ($boll_pc = 0 OR tp.id_bollettino = $boll_pc)
						ORDER BY u.telegram_id, tp.data_invio DESC, tp.data_invio_conv DESC) AS subquery
				WHERE tc.id =subquery.id;";

	} else {
		$query0="UPDATE users.t_convocazione tc
				SET data_invio_conv = date_trunc('hour', now()) + date_part('minute', now())::int / 10 * interval '10 min', lettura_conv = null, data_conferma_conv = null 
				FROM (SELECT DISTINCT ON (u.telegram_id) u.matricola_cf,
										u.nome,
										u.cognome,
										jtfc.funzione,
										u.telegram_id,
										tp.id,
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
						WHERE ($boll_pc = 0 OR tp.id_bollettino = $boll_pc)
						ORDER BY u.telegram_id, tp.data_invio DESC, tp.data_invio_conv DESC) AS subquery
				WHERE tc.id =subquery.id;";
	}
}
$result0 = pg_prepare($conn, "myquery0", $query0);
$result0 = pg_execute($conn, "myquery0", array());

$query1='SELECT telegram_id from users.utenti_coc;';
$result1 = pg_prepare($conn, "myquery1", $query1);
$result1 = pg_execute($conn, "myquery1", array());
while($r = pg_fetch_assoc($result1)) {
	  
	  	$keyboard = [
			'inline_keyboard' => [
				[
					['text' => 'OK', 'callback_data' => 'convocazione']
				]
			]
		];
		$encodedKeyboard = json_encode($keyboard);
		$parameters = 
			array(
				'chat_id' => $r['telegram_id'], 
				'text' => $testo, 
				'reply_markup' => $encodedKeyboard
			);
		
		sendButton('sendMessage', $parameters, $tokencoc);
  }

header("Location: elenco_coc.php");
exit();


?>
