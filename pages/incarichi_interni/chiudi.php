<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


//echo "<h2> La gestione degli incarichi interni e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2>";


//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //incarico

$id_lavorazione=$_POST["id_lavorazione"];
$note_rifiuto= str_replace("'", "''", $_POST["note_rifiuto"]);
$uo=$_POST["uo"];
$squadra=$_POST["squadra"];


echo "Incarico:".$id. "<br>";


//exit;


$query= "UPDATE segnalazioni.t_incarichi_interni SET note_ente='".$note_rifiuto."', time_stop=now() 
WHERE id=".$id.";";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);



$query= "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico";

//values
$query=$query.") VALUES (".$id.", 3 ";

$query=$query.");";

echo $query."<br>";
//exit;
$result=pg_query($conn, $query);


$query="UPDATE users.t_squadre SET id_stato=2 WHERE id=".$squadra.";";
echo $query;
//exit;
$result=pg_query($conn, $query);

/* $query= "SELECT id_profilo, max(id_segnalazione) as id_segn FROM segnalazioni.v_incarichi_interni WHERE id = ".$id." group by id_profilo;";
$result=pg_query($conn, $query);

while($r = pg_fetch_assoc($result)) {
	$id_profilo=$r['id_profilo'];
	$id_segnalazione=$r['id_segn'];
}


require('../token_telegram.php');

require('../send_message_telegram.php');


if ($id_profilo == '3'){
	$query_telegram="SELECT telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';";
} else {
	$query_telegram="SELECT telegram_id from users.utenti_sistema where id_profilo = '".$id_profilo."' and telegram_id !='' and telegram_attivo='t';";
}
#echo $query_telegram;
echo "<br>";

// https://apps.timwhitlock.info/emoji/tables/unicode
// \xE2\x9A\xA0 warning
// \xE2\x80\xBC punti esclamativi

$messaggio="\xE2\x9A\xA0 L'incarico interno assegnato sulla segnalazione con id = ".$id_segnalazione.", è stato chiuso con le seguenti note: ";
$messaggio= $messaggio ." \xE2\x84\xB9 ".$note_rifiuto."";

echo $messaggio;
echo "<br>";
$result_telegram = pg_query($conn, $query_telegram);
while($r_telegram = pg_fetch_assoc($result_telegram)) {
	//echo $r_telegram['telegram_id'];
	//$chat_id = $r_telegram['telegram_id'];
	sendMessage($r_telegram['telegram_id'], $messaggio , $token);
} */

$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento";

//values
$query=$query.") VALUES (".$id_lavorazione.", ' Incarico interno ".$id." chiuso dalla seguente squadra: ".$uo." con il seguente messaggio: <br><i>".$note_rifiuto." </i><br>- <a class=\"btn btn-info\" href=\"dettagli_incarico_interno.php?id=".$id."\"> Visualizza dettagli </a>'";

$query=$query.");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Incarico interno ".$id." chiuso');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


//exit;
header("location: ../dettagli_incarico_interno.php?id=".$id);


?>
