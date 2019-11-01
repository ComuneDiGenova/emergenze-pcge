<?php

session_start();

include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';

$id=$_GET["id"];

$id=str_replace("'", "", $id);

$query="UPDATE eventi.t_eventi SET fine_sospensione=now() + interval '8 hours' where id=$id;";
//echo $query;
//exit;
$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["operatore"] ."', 'Sospensione evento ".$id."');";
$result = pg_query($conn, $query_log);



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

$messaggio="\xF0\x9F\x9A\xAB L'evento di tipo ".$descrizione_tipo." (id=".$id.") è stato sospeso per le prossime 8 ore.";
$messaggio= $messaggio ." (ricevi questo messaggio in quanto operatore di Protezione Civile)";
$messaggio= $messaggio ." \xF0\x9F\x9A\xAB";

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
//echo "<br>";
echo $query_log;

//exit;
//header("location: ../dettagli_evento.php");


?>