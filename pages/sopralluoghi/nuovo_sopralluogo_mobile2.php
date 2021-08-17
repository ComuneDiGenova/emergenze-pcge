<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

require('../check_evento.php');
//$profilo_ok= $_SESSION['profilo_ok'];


if($profilo_ok==8 and $uo_inc=='uo_1' ){
	$profilo_ok=3;
}


//$id=$_GET["id"];
//$id=str_replace("'", "", $_GET['id']); //segnazione in lavorazione
//$segn=str_replace("'", "", $_GET['s']); //segnazione in lavorazione

$descrizione= str_replace("'", "''", $_POST["descrizione"]);

$uo = str_replace("'", "''", $_POST["uo"]);
$id_squad = pg_escape_string($_POST["uo"]);
$idn_squad=(int)$id_squad;
$id_evento = str_replace("'", "''", $_POST["evento"]);

$percorso = $_POST["percorso"];



//echo "Segnalazione in lavorazione:".$id. "<br>";
//echo "Segnalazione:".$segn. "<br>";
//echo "Descrizione:".$descrizione. "<br>";
//echo "Squadra:".$uo. "<br>";



//echo "<h2>La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2> <br> ";




$query_max= "SELECT max(id) FROM segnalazioni.t_sopralluoghi_mobili;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_sopralluogo=$r_max["max"]+1;
	} else {
		$id_sopralluogo=1;	
	}
}
//echo "Id sopralluogo:".$id_sopralluogo. "<br>";



// recupero info su U.O. 

$query_uo= "SELECT * FROM users.v_squadre where id='".$uo."';";


$result_uo = pg_query($conn, $query_uo);
while($r_uo = pg_fetch_assoc($result_uo)) {
	$uo_descrizione=$r_uo['nome'];
}
//echo $query_uo."<br>";


$query_percorso= "SELECT st_transform (geom,4326) as geom FROM geodb.v_presidi_mobili WHERE percorso='".$_POST["percorso"]."';";
//echo $query_percorso;
echo "<br>";
// se ci fossero problemi con il valore 'geom' controlla l record corrispondente nella tabella geodb.m_tables, 
// che gestisce il trasferimento dati da Oracle a postgis
$result_percorso=pg_query($conn, $query_percorso);
while($rc = pg_fetch_assoc($result_percorso)) {
	$geom="'".$rc["geom"]."'"; // messo fra apici per poi includerlo nella successiva query	
}




$query="UPDATE users.t_squadre SET id_stato=1 WHERE id=".$uo.";";
//echo $query;
//exit;
$result=pg_query($conn, $query);


// metti un check
/*$query= "SELECT * FROM segnalazioni.t_sopralluoghi_mobili 
where descrizione = ".$percorso." and data_fine is null;";
$result=pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	echo "ATTENZIONE: il percorso risulta gi� inserito. Probabilmente hai schiacciato due volte il tasto";
	echo '<br>Entro 10" verrai re-indirizzato alla pagina del presidio mobile, clicca al seguente ';
	echo '<a href="../dettagli_sopralluogo_mobile.php?id='.$id_sopralluogo.'">link</a> per saltare l\'attesa.</h3>' ;
	//sleep(30);
    header("refresh:10;url=../dettagli_sopralluogo_mobile.php?id=".$id_sopralluogo);
	
}*/



$query= "INSERT INTO segnalazioni.t_sopralluoghi_mobili ( id, descrizione, id_profilo, id_evento, geom";

if ($descrizione!=''){
$query=$query.",note_ente";
}

//values
$query=$query.") VALUES (".$id_sopralluogo.", '".$percorso."', '".$profilo_ok."',  ". $id_evento. ", ". $geom." ";
if ($descrizione!=''){
$query=$query.",'".$descrizione."'";
}
$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);
//exit;



if($_POST["permanente"]=='on') {
	$query="UPDATE segnalazioni.t_sopralluoghi_mobili SET time_preview=now(), time_start=now() WHERE id=".$id_sopralluogo.";";
	$result=pg_query($conn, $query);
	$query= "INSERT INTO segnalazioni.stato_sopralluoghi_mobili(id_sopralluogo, id_stato_sopralluogo";

	//values
	$query=$query.") VALUES (".$id_sopralluogo.", 2 ";

	$query=$query.");";

	//echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
	$messaggio="\xE2\x80\xBC E' stato assegnato un nuovo presidio mobile con accettazione automatica alla squadra di tua appartenenza ".$uo_descrizione." con i seguenti dettagli:".$descrizione."\n";

} else {
	$query= "INSERT INTO segnalazioni.stato_sopralluoghi_mobili(id_sopralluogo, id_stato_sopralluogo";
	//values
	$query=$query.") VALUES (".$id_sopralluogo.", 1 ";
	$query=$query.");";
	//echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);

	$messaggio="\xE2\x80\xBC E' stato assegnato un nuovo presidio mobile alla squadra di tua appartenenza ".$uo_descrizione." con i seguenti dettagli:".$descrizione."\n";
	$messaggio= $messaggio ." Visualizza i dettagli del presidio accedendo con le tue credenziali al Sistema di Gestione delle Emergenze del Comune di Genova.";
}



echo "<br>";


$query= "INSERT INTO segnalazioni.join_sopralluoghi_mobili_squadra (id_sopralluogo,id_squadra ";
$query=$query.") VALUES (".$id_sopralluogo.",".$uo.");";
//echo $query;
//exit;
$result=pg_query($conn, $query);



echo "<br>";
/*$query= "INSERT INTO segnalazioni.join_segnalazioni_sopralluoghi(id_sopralluogo, id_segnalazione_in_lavorazione";

//values
$query=$query.") VALUES (".$id_sopralluogo.", ".$id." ";

$query=$query.");";



//echo $query;
//exit;
$result=pg_query($conn, $query);
*/




/*$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione( id_segnalazione_in_lavorazione, log_aggiornamento";

//values
$query=$query.") VALUES (".$id.", ' Assegnato nuovo sopralluogo alla seguente squadra: ".$uo_descrizione." - <a class=\"btn btn-info\" href=\"dettagli_sopralluogo.php?id=".$id_sopralluogo."\"> Visualizza dettagli </a>'";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);*/


$query_log= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('segnalazioni','".$_SESSION["operatore"] ."', 'Inviato presidio/sopralluogo ".$id_sopralluogo."');";
$result = pg_query($conn, $query_log);




//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
//echo $query_log;

/* $query="SELECT mail FROM users.t_mail_squadre WHERE cod='".$uo."';";
$result=pg_query($conn, $query);
$mails=array();
while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
} */

require('../token_telegram.php');

require('../send_message_telegram.php');

$query='SELECT u.telegram_id, u.telegram_attivo, s.mail
FROM users.v_componenti_squadre vcs
left join users.v_utenti_sistema u 
on u.matricola_cf = vcs.matricola_cf 
left join (select matricola_cf, mail from users.t_mail_squadre s where cod=$1) as s
on vcs.matricola_cf = s.matricola_cf
where id=$2 and data_end is null;';
$result = pg_prepare($conn, "myquery0", $query);
$result = pg_execute($conn, "myquery0", array($uo, $idn_squad));

$mails=array();
//$telegram_id=array();
#$messaggio="\xE2\x80\xBC E' stato assegnato un nuovo presidio fisso alla squadra di tua appartenenza ".$uo_descrizione." con i seguenti dettagli:".$descrizione."\n";
#$messaggio= $messaggio ." \xF0\x9F\x91\x8D per accettare l'incarico digita /presidio ";

while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
  //array_push($telegram_id,$r['telegram_id']);
  if($r['telegram_id']!='' && $r['telegram_attivo']=='t'){
	sendMessage($r['telegram_id'], $messaggio , $token);
  }
}

echo "<br>";
//echo $query;
//echo "<br>";
echo count($mails). " mail registrate a sistema";

//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/PHPMailer/src/Exception.php';
require '../../vendor/PHPMailer/src/PHPMailer.php';
require '../../vendor/PHPMailer/src/SMTP.php';


//echo "<br>OK 1<br>";
//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');
//require '../../vendor/autoload.php';
//Create a new PHPMailer instance
$mail = new PHPMailer;

//echo "<br>OK 1<br>";
//Tell PHPMailer to use SMTP
$mail->isSMTP();
//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 0;
//Set the hostname of the mail server

// host and port on the file credenziali_mail.php
require './credenziali_mail.php';


//Set who the message is to be sent from
$mail->setFrom('salaemergenzepc@comune.genova.it', 'Sala Emergenze PC Genova');
//Set an alternative reply-to address
$mail->addReplyTo('no-reply@comune.genova.it', 'No Reply');
//Set who the message is to be sent to

//$mails=array('vobbo@libero.it','roberto.marzocchi@gter.it');
while (list ($key, $val) = each ($mails)) {
  $mail->AddAddress($val);
}
//Set the subject line
$mail->Subject = 'Urgente - Nuovo presidio mobile assegnato tramite il Sistema di Gestione Emergenze del Comune di Genova';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  'Hai ricevuto questo messaggio in quanto � stato assegnato un nuovo presidio alla squadra di tua appartenenza 
 '.$uo_descrizione.'. <br> Ti preghiamo di non rispondere a questa mail, ma di visualizzare i dettagli del presidio mobile accedendo 
 con le tue credenziali al nuovo <a href="http://192.168.153.110/emergenze/pages/dettagli_sopralluogo_mobile.php?id='.$id_sopralluogo.'" > Sistema di Gestione delle Emergenze </a> del Comune di Genova.
 <br> <br> Protezione Civile del Comune di Genova. <br><br>--<br> Ricevi questa mail  in quanto il tuo indirizzo mail � registrato a sistema. 
 Per modificare queste impostazioni � possibile inviare una mail a salaemergenzepc@comune.genova.it ';


  
require('../informativa_privacy_mail.php');

$mail-> Body=$body ;


//$mail->Body =  'Corpo del messaggio';
//$mail->msgHTML(file_get_contents('E\' arrivato un nuovo sopralluogo da parte del Comune di Genova. Visualizza lo stato del sopralluogo al seguente link e aggiornalo quanto prima. <br> Ti chiediamo di non rispondere a questa mail'), __DIR__);
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');
//send the message, check for errors
//echo "<br>OK 2<br>";
if (!$mail->send()) {
    //echo "<h3>Problema nell'invio della mail: " . $mail->ErrorInfo;
    echo "<h3>Problema nell'invio della mail: ";
	?>
	<!--script> alert(<?php echo "Problema nell'invio della mail: " . $mail->ErrorInfo;?>) </script-->
	<?php
	//echo '<br>Il presidio &egrave stato correttamente assegnato, ma si &egrave riscontrato un problema nell\'invio della mail.';
	echo '<div style="text-align: center;"><img src="../../img/no_mail.png" width="75%" alt=""></div>';
	echo '<br><h1>Entro 15" verrai re-indirizzato alla pagina del presidio mobile, clicca al seguente ';
	echo '<a href="../dettagli_sopralluogo_mobile.php?id='.$id_sopralluogo.'">link</a> per saltare l\'attesa.</h1>' ;
	//sleep(30);
    header("refresh:12;url=../dettagli_sopralluogo_mobile.php?id=".$id_sopralluogo);
} else {
    echo "Message sent!";
	header("location: ../dettagli_sopralluogo_mobile.php?id=".$id_sopralluogo);
}



//exit;
//header("location: ../dettagli_segnalazione.php?id=".$segn);


?>
