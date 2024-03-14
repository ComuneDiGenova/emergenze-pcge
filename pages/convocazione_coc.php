<?php

session_start();
require('./validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


//$id=$_GET["id"];
//echo $_POST['testoCoC'];
$testo=str_replace("'", "''", $_POST['testoCoC']); 
//echo $testo;

require('./token_telegram.php');

require('./send_message_telegram.php');

$query="SELECT DISTINCT ON (u.telegram_id) u.matricola_cf,
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
							tp.lettura_conv 
		FROM users.utenti_coc u
		RIGHT JOIN users.t_convocazione tp 
			ON u.telegram_id::text = tp.id_telegram::text
		JOIN users.tipo_funzione_coc jtfc 
			ON jtfc.id = u.funzione
		ORDER BY u.telegram_id, tp.data_invio DESC;";

$result = pg_prepare($conn, "myquery", $query);
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
									tp.lettura_conv 
						FROM users.utenti_coc u
						RIGHT JOIN users.t_convocazione tp 
							ON u.telegram_id::text = tp.id_telegram::text
						JOIN users.tipo_funzione_coc jtfc 
							ON jtfc.id = u.funzione
						ORDER BY u.telegram_id, tp.data_invio DESC) AS subquery
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
										tp.lettura_conv 
						FROM users.utenti_coc u
						RIGHT JOIN users.t_convocazione tp 
							ON u.telegram_id::text = tp.id_telegram::text
						JOIN users.tipo_funzione_coc jtfc 
							ON jtfc.id = u.funzione
						ORDER BY u.telegram_id, tp.data_invio DESC) AS subquery
				WHERE tc.id =subquery.id;";
	}
}
$result0 = pg_prepare($conn, "myquery0", $query0);
$result0 = pg_execute($conn, "myquery0", array());

$query1='SELECT telegram_id from users.utenti_coc;';
$result1 = pg_prepare($conn, "myquery1", $query1);
$result1 = pg_execute($conn, "myquery1", array());
while($r = pg_fetch_assoc($result1)) {
	  //sendMessage($r['telegram_id'], $testo , $tokencoc);
	  
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


// // $query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Inviata comunicazione a PC (incarico interno ".$id.")');";
// // echo $query_log."<br>";
// // $result = pg_query($conn, $query_log);


// //exit;
header("location: ./elenco_coc.php");


?>