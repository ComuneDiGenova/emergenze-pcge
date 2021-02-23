<?php

session_start();
//require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=pg_escape_string($_GET["id"]);

$id=str_replace("'", "", $id);

$query="UPDATE eventi.t_eventi SET data_ora_fine_evento=now(), valido='FALSE' where id=$id;";
echo $query;
//exit;
$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Chiusura evento definitiva ".$_POST['id']."');";
$result = pg_query($conn, $query_log);


//chiudi squadre

$query0= "SELECT c.matricola_cf from users.t_componenti_squadre c
JOIN users.t_squadre s ON s.id=c.id_squadra
WHERE s.id_evento=".$id." and c.data_end is null;";
$result0 = pg_query($conn, $query0);
while($r0 = pg_fetch_assoc($result0)) {
	$query1="UPDATE users.t_componenti_squadre 
	SET data_end=now() 
	WHERE matricola_cf = '".$r0['matricola_cf']."';";
	$result1 = pg_query($conn, $query1);
}




//notifiche telegram 
require('../token_telegram.php');

require('../send_message_telegram.php');



$query="SELECT descrizione FROM eventi.v_eventi WHERE id=".$id.";";
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$descrizione_tipo=$r['descrizione'];
}


$query_telegram="SELECT telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';";

//echo $query_telegram;
//echo "<br>";

// https://apps.timwhitlock.info/emoji/tables/unicode
// \xE2\x9A\xA0 warning
// \xE2\x80\xBC punti esclamativi

$messaggio=" \xF0\x9F\x94\xB4 L'evento di tipo ".$descrizione_tipo." (id=".$id.") e' stato chiuso in maniera definitiva.";
$messaggio= $messaggio ." (ricevi questo messaggio in quanto operatore di Protezione Civile)";
$messaggio= $messaggio ." \xF0\x9F\x94\xB4 ";

echo $messaggio;
echo "<br>";
$result_telegram = pg_query($conn, $query_telegram);
while($r_telegram = pg_fetch_assoc($result_telegram)) {
	//echo $r_telegram['telegram_id'];
	//$chat_id = $r_telegram['telegram_id'];
	sendMessage($r_telegram['telegram_id'], $messaggio , $token);
}





//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_evento_c.php");


?>