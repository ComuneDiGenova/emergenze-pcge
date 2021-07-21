<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require ('../note_ambiente.php');



$query_max= "SELECT max(id) FROM eventi.t_eventi;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$new_id=$r_max["max"]+1;
	} else {
		$new_id=1;	
	}
}

echo "id nuovo evento = ".$new_id;
echo "id = ".$id;
echo "<br>";


$query="INSERT INTO eventi.t_eventi (id) VALUES(".$new_id.")";
echo $query;
$result = pg_query($conn, $query);
echo "<br>";


$query="INSERT INTO eventi.join_tipo_evento (id_evento, id_tipo_evento) VALUES(".$new_id.", ".$_POST["tipo_evento"].")";
echo $query;
$result = pg_query($conn, $query);
echo "<br>";





$check = isset($_POST['check']) ? $_POST['check'] : array();
foreach($check as $municipio) {
  //echo $municipio . '<br/>';
  $query="INSERT INTO eventi.join_municipi (id_evento, id_municipio, data_ora_inizio) VALUES(".$new_id.", ".$municipio.",now())";
  echo $query . '<br/>';
  $result = pg_query($conn, $query);
}






if ($_POST["note"]){
	$query="INSERT INTO eventi.t_note_eventi (id_evento, nota) VALUES(".$new_id.", '".$_POST["note"]."')";
	echo $query;
	$result = pg_query($conn, $query);
}



//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["operatore"] ."', 'Creazione evento n. ".$new_id."');";
$result = pg_query($conn, $query_log);


$query= "SELECT notifiche, descrizione FROM eventi.tipo_evento where id= ".$_POST['tipo_evento'].";";
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$notifiche=$r['notifiche'];
	$descrizione_tipo=$r['descrizione'];
}

echo $notifiche;
//exit;
//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

require('../token_telegram.php');

require('../send_message_telegram.php');


if ($notifiche =='t'){
	$query_telegram="SELECT telegram_id from users.utenti_sistema where telegram_id !='' and telegram_attivo='t';";
} else {
	$query_telegram="SELECT telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';";
}
echo $query_telegram;
echo "<br>";

// https://apps.timwhitlock.info/emoji/tables/unicode
// \xE2\x9A\xA0 warning
// \xE2\x80\xBC punti esclamativi

$messaggio="\xE2\x9A\xA0 \xF0\x9F\x86\x95 E' stato creato un nuovo evento di tipo ".$descrizione_tipo." (id=".$new_id."), consultare il programma ".$link." ";
if ($notifiche =='f'){
	$messaggio= $messaggio ." (\xE2\x84\xB9 ricevi questo messaggio in quanto operatore di Protezione Civile \xE2\x84\xB9)";
}
$messaggio= $messaggio ."\xE2\x9A\xA0 ";

echo $messaggio;
echo "<br>";
$result_telegram = pg_query($conn, $query_telegram);
while($r_telegram = pg_fetch_assoc($result_telegram)) {
	//echo $r_telegram['telegram_id'];
	//$chat_id = $r_telegram['telegram_id'];
	sendMessage($r_telegram['telegram_id'], $messaggio , $token);
}


//Import the PHPMailer class into the global namespace
use PHPMailer\PHPMailer\PHPMailer;

if ($notifiche =='t') {
	$query="SELECT mail FROM users.t_mail_incarichi
	where cod not ilike 'com_MU%' or cod ilike ";
	foreach($check as $municipio) {
	  $query=$query." 'com_MU00".$municipio."' or cod ilike ";
	}
	$query=$query." 'test'";
	echo $query;
	//exit;



	$result=pg_query($conn, $query);
	$mails=array();
	while($r = pg_fetch_assoc($result)) {
	  array_push($mails,$r['mail']);
	}

	echo "<br>";
	//echo $query;
	//echo "<br>";
	echo "<br>".count($mails). " mail registrate a sistema</h3>";

	

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
	require '../incarichi/credenziali_mail.php';


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
	$mail->Subject = 'Nuovo evento creato dalla Protezione Civile del Comune di Genova su Sistema Emergenze '. $note_ambiente_mail;
	//$mail->Subject = 'PHPMailer SMTP without auth test';
	//Read an HTML message body from an external file, convert referenced images to embedded,
	//convert HTML into a basic plain-text alternative body
	$body =  'La tua unit&agrave operativa ha ricevuto questo messaggio automaticamente in quanto &egrave stato creato un nuovo evento di tipo '.$descrizione_tipo.' (id='.$new_id.') da parte 
	della Protezione Civile. <br> Stampa '.$id.'###<br>
	 Ti preghiamo di non rispondere a questa mail, ma di avvisare chi di dovere perch&egrave il sistema venga mantenuto sotto controllo.  <br>
	 Per accedere al nuovo <a href="https;//emergenze.comune.genova.it/pages/index.php" > Sistema di Gestione delle Emergenze </a> del Comune di Genova &egrave necessaria
	 la matricola personale (personale comunale) o le credenziale SPID. Occorre inoltre essere abilitati all\'accesso da parte della Protezione Civile.
	 <br> <br> Protezione Civile del Comune di Genova. <br><br>--<br> Ricevi questa mail  in quanto il tuo indirizzo mail &egrave registrato a sistema. 
	 Per modificare queste impostazioni o richiedere l\'accesso al sistema &egrave possibile contattare gli amministratori 
	 inviando una mail a adminemergenzepc@comune.genova.it inoltrando il presente messaggio. Ti ringraziamo per la preziosa collaborazione.';


	  
	require('../informativa_privacy_mail.php');

	$mail-> Body=$body ;


	//$mail->Body =  'Corpo del messaggio';
	//$mail->msgHTML(file_get_contents('E\' arrivato un nuovo incarico da parte del Comune di Genova. Visualizza lo stato dell\'incarico al seguente link e aggiornalo quanto prima. <br> Ti chiediamo di non rispondere a questa mail'), __DIR__);
	//Replace the plain text body with one created manually
	$mail->AltBody = 'This is a plain-text message body';
	//Attach an image file
	//$mail->addAttachment('images/phpmailer_mini.png');
	//send the message, check for errors
	//echo "<br>OK 2<br>";
	if (!$mail->send()) {
		//entra in questo if anche se viene inviata la mail perchè non riesce a inviarla a uno dei contatti (cristina olivieri) per capire decpmmentare riga sotto
		//echo "<h3>Problema nell'invio della mail: " . $mail->ErrorInfo;
		echo "<h3>Problema nell'invio della mail";

		echo '<br>L\'incarico &egrave stato correttamente assegnato, ma si &egrave riscontrato un problema nell\'invio della mail.';
		echo '<br>Entro 15" verrai re-indirizzato alla pagina della tua segnalazione, clicca al seguente <a href="../dettagli_evento.php">link</a> per saltare l\'attesa.</h3>';
		//$id variabile non definita, probabilmente riferita a vecchio codice
		//passiamo newid alla url del reindirizzamento --> da semplificare/rimuovere if perchè non avendo più l'id non hanno senso

		/* if ($id!=''){
			echo '<a href="../dettagli_evento.php">link</a> per saltare l\'attesa.</h3>' ;
		} else {
			echo '<a href="../dettagli_evento.php">link</a> per saltare l\'attesa.</h3>' ;
		} */

		header("refresh:15;url=../dettagli_evento.php?e=".$new_id);
		
		//sleep(30);
		/* if ($id!=''){
			header("refresh:15;url=../dettagli_evento.php?e=".$new_id);
		} else {
			header("refresh:15;url=../dettagli_evento.php?e=".$new_id);
		} */
	} else {
		echo "Message sent correctly!";
		header("location:../dettagli_evento.php?e=".$new_id);
		/* if ($id!=''){
			header("location:../dettagli_evento.php?e=".$new_id);
		} else {
			header("location:../dettagli_evento.php?e=".$new_id);
		} */
	}
} else {
	header("location:../dettagli_evento.php?e=".$new_id);
}



//exit;
//header("location: ../dettagli_evento.php");


?>