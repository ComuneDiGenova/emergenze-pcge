<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


echo "<h2> La gestione degli incarichi interni e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2>";


//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //incarico


$id_lavorazione=$_POST["id_lavorazione"];
$data_inizio=$_POST["data_inizio"];
$hh=$_POST["hh_start"];
$mm=$_POST["mm_start"];

$time_preview= $data_inizio." ".$hh .":".$mm;
 
$parziale=$_POST["parziale"];

$note= str_replace("'", "''", $_POST["note"]);
$uo=$_POST["uo"];

echo "Incarico:".$id. "<br>";
echo "Time preview:".$time_preview. "<br>";
echo "Note:".$note. "<br>";
echo "Parziale:".$parziale. "<br>";

//exit;


$query= "UPDATE segnalazioni.t_incarichi_interni SET time_preview='".$time_preview."' 
WHERE id=".$id.";";
echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";


$query= "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico, parziale";

//values
$query=$query.") VALUES (".$id.", 2 , '".$parziale."'";

$query=$query.");";

echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";



if ($note!=''){

	$query= "INSERT INTO segnalazioni.t_comunicazioni_incarichi_interni(
	            id_incarico, testo)
	    VALUES (".$id.", '".$note."');";
	
	
	echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
	echo "Result:". $result."<br>";
}

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

$messaggio="\xE2\x9A\xA0 L'incarico interno assegnato sulla segnalazione con id = ".$id_segnalazione.", è stato accettato";
if ($note!=''){
	$messaggio= $messaggio ." con le seguenti note: ";
	$messaggio= $messaggio ." \xE2\x84\xB9 ".$note."";
}

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
if ($parziale=='t'){
	$query=$query.") VALUES (".$id_lavorazione.", ' Incarico interno".$id." preso in carico (parzialmente) dalla seguente squadra.: ".$uo." - <a class=\"btn btn-info\" href=\"dettagli_incarico_interno.php?id=".$id."\"> Visualizza dettagli </a>'";
} else {
	$query=$query.") VALUES (".$id_lavorazione.", ' Incarico interno".$id." preso in carico dalla seguente squadra.: ".$uo." - <a class=\"btn btn-info\" href=\"dettagli_incarico_interno.php?id=".$id."\"> Visualizza dettagli </a>'";
}
$query=$query.");";


echo $query."<br>";
//exit;

$result=pg_query($conn, $query);
echo "Result:". $result."<br>";




$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Incarico interno ".$id." preso in carico');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


echo "Result:". $result."<br>";



//exit;
header("location: ../dettagli_incarico_interno.php?id=".$id);


?>