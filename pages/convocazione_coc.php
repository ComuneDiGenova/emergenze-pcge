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
$result_tg_ids = pg_prepare($conn, "get_tg_ids", $query_tg_ids);
$result_tg_ids = pg_execute($conn, "get_tg_ids", array());

// Estrai tutti gli id telegram come array
$tg_ids = pg_fetch_all($result_tg_ids);

// ottengo il nuovo valore di id_convocazione
$query_id_convocazione = "SELECT COALESCE(
								(SELECT id_convocazione 
								FROM users.t_lettura_conv_coc
								ORDER BY id_convocazione DESC
								LIMIT 1),
								0
							) AS id_convocazione;";

// Estraggo l'id_convocazione
$result = pg_query($conn, $query_id_convocazione);
$row = pg_fetch_assoc($result);
$id_convocazione = $row['id_convocazione'] + 1;

$values = [];
foreach ($tg_ids as $row) {
	$chat_id_coc = $row['telegram_id'];

	// Creo convocazione senza inserire bollettino
	$values[] = "(
		$id_convocazione,
		date_trunc('hour', now()) + date_part('minute', now())::int / 10 * interval '10 min',
		'$chat_id_coc'
	)";
}


// Unisco tutti i valori in una singola stringa separata da virgole
$values_string = implode(", ", $values);

$query = "INSERT INTO users.t_lettura_conv_coc (id_convocazione, data_invio_conv, id_telegram) 
			VALUES $values_string;";
// Eseguo la query
$result = pg_query($conn, $query);

echo $boll_pc;
echo "\n";
echo $id_convocazione;
echo "\n";



// Invio messaggio telegram per ogni telegram id del coc
// while($r = pg_fetch_assoc($result_tg_ids)) {
	  
// 	  	$keyboard = [
// 			'inline_keyboard' => [
// 				[
// 					['text' => 'OK', 'callback_data' => 'convocazione']
// 				]
// 			]
// 		];
// 		$encodedKeyboard = json_encode($keyboard);
// 		$parameters = 
// 			array(
// 				'chat_id' => $r['telegram_id'], 
// 				'text' => $testo, 
// 				'reply_markup' => $encodedKeyboard
// 			);
		
// 		sendButton('sendMessage', $parameters, $tokencoc);
//   }

// header("Location: elenco_coc.php");
// exit();


?>
