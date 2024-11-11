<?php

session_start();
require('./validate_input.php');
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$testo=str_replace("'", "''", $_POST['testoCoC']);
$boll_pc = $_POST['boll_pc'];

require('./token_telegram.php');
require('./send_message_telegram.php');


// Funzione per eseguire le query
function execute_query($conn, $query) {
    $result = pg_query($conn, $query);
    if (!$result) {
        error_log("Errore nella query: " . pg_last_error($conn));
        exit("Errore nella query");
    }
    return $result;
}

// Ottengo gli ID Telegram
$query_tg_ids = "SELECT DISTINCT ON (u.telegram_id) u.telegram_id
				 FROM users.utenti_coc u;";

$result_tg_ids = execute_query($conn, $query_tg_ids);
$tg_ids = pg_fetch_all($result_tg_ids);

// Ottengo il nuovo valore di id_convocazione
$query_id_convocazione = "SELECT COALESCE(
								(SELECT id_convocazione 
								FROM users.t_lettura_conv_coc
								ORDER BY id_convocazione DESC
								LIMIT 1),
								0
							) AS id_convocazione;";

$result = pg_query($conn, $query_id_convocazione);
$row = pg_fetch_assoc($result);
$id_convocazione = $row['id_convocazione'] + 1;

// Creo i record in `t_lettura_conv_coc`
$values = array_map(function($row) use ($id_convocazione, $boll_pc) {
    $chat_id_coc = $row['telegram_id'];
    $id_bollettino_value = $boll_pc != 0 ? $boll_pc : 'NULL';
    return "($id_convocazione, date_trunc('hour', now()) + date_part('minute', now())::int / 10 * interval '10 min', '$chat_id_coc', $id_bollettino_value)";
}, $tg_ids);


// Unisco tutti i valori in una singola stringa separata da virgole
$values_string = implode(", ", $values);

$query = "INSERT INTO users.t_lettura_conv_coc (id_convocazione, data_invio_conv, id_telegram, id_bollettino) 
			VALUES $values_string;";

execute_query($conn, $query);


// Tento di aggiornare `t_lettura_bollettino`
if ($boll_pc != 0) {
    $query_update_bollettino = "UPDATE users.t_lettura_bollettino
        						SET id_convocazione = $id_convocazione
        						WHERE id_bollettino = $boll_pc 
          							AND id_convocazione IS NULL;";

	$result_update = pg_query($conn, $query_update_bollettino);

	// se l'UPDATE non ha aggiornato record duplico le righe
	if (pg_affected_rows($result_update) === 0) { 
		$query_duplicate_rows = "INSERT INTO users.t_lettura_bollettino (id_bollettino, id_telegram, data_invio, data_conferma, lettura, id_convocazione)
        						SELECT id_bollettino, id_telegram, data_invio, data_conferma, lettura, NULL
        						FROM users.t_lettura_bollettino
        						WHERE id_bollettino = $boll_pc;";
		execute_query($conn, $query_duplicate_rows);

		// Eseguo di nuovo l'UPDATE per assegnare l'id_convocazione ai nuovi record
		execute_query($conn, $query_update_bollettino);
	}
}

// Invio messaggio telegram per ogni telegram id del coc
foreach ($tg_ids as $row) {
    $chat_id_coc = $row['telegram_id'];
    $keyboard = [
        'inline_keyboard' => [
            [['text' => 'OK', 'callback_data' => 'convocazione']]
        ]
    ];
    $encodedKeyboard = json_encode($keyboard);
    $parameters = [
        'chat_id' => $chat_id_coc,
        'text' => $testo,
        'reply_markup' => $encodedKeyboard
    ];
    sendButton('sendMessage', $parameters, $tokencoc);
}

header("Location: elenco_coc.php");
exit();


?>
