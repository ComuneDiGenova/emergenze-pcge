<?php


session_start();
require('../validate_input.php');

header('Content-Type: text/html; charset=utf-8');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

require('../check_evento.php');



//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //segnazione in lavorazione

$segn=str_replace("'", "", $_GET['s']); //segnazione in lavorazione



$id_pc=str_replace("'", "", $_GET['id_pc']); //segnazione in lavorazione




$descrizione= str_replace("'", "''", $_POST["descrizione"]);
$uo= str_replace("'", "''", $_POST["uo"]);


//echo "Segnalazione in lavorazione:".$id. "<br>";
//echo "Segnalazione:".$segn. "<br>";
//echo "Descrizione:".$descrizione. "<br>";
//echo "Unita_operativa:".$uo. "<br>";



//echo "<h2>La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2> <br> ";





$query_max= "SELECT max(id) FROM segnalazioni.t_incarichi;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_incarico=$r_max["max"]+1;
	} else {
		$id_incarico=1;	
	}
}
//echo "Id incarico:".$id_incarico. "<br>";



// recupero info su U.O. 
$uo_array=explode("_",$uo);
if ($uo_array[0]=='com'){
  $query_uo= "SELECT * FROM varie.incarichi_comune where cod='".$uo_array[1]."';";
} else if ($uo_array[0]=='uo'){
  $query_uo= "SELECT * FROM users.uo_1_livello where id1=".$uo_array[1].";";
};

$result_uo = pg_query($conn, $query_uo);
while($r_uo = pg_fetch_assoc($result_uo)) {
	$uo_descrizione=$r_uo['descrizione'];
}
//echo $query_uo."<br>";

//echo "Descrizione uo:".$uo_descrizione. "<br>";



$query= "INSERT INTO segnalazioni.t_incarichi( id, descrizione, id_profilo, id_UO";

//values
$query=$query.") VALUES (".$id_incarico.", '".$descrizione."', '".$profilo_ok."', '".$uo."' ";

$query=$query.");";

//echo $query."<br>";
//exit;
$result=pg_query($conn, $query);

echo "<br>";

if ($id!=''){
	$query= "INSERT INTO segnalazioni.join_segnalazioni_incarichi(id_incarico, id_segnalazione_in_lavorazione";
	
	//values
	$query=$query.") VALUES (".$id_incarico.", ".$id." ";
	
	$query=$query.");";
	
	//echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
} else if($id_pc!='') {
	$query= "INSERT INTO segnalazioni.join_incarico_provvedimenti_cautelari(id_incarico, id_provvedimento";
	
	//values
	$query=$query.") VALUES (".$id_incarico.", ".$id_pc." ";
	
	$query=$query.");";
	
	//echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);

} else {
	echo "Problema join";
	exit;
}

$query= "INSERT INTO segnalazioni.stato_incarichi(id_incarico, id_stato_incarico";

//values
$query=$query.") VALUES (".$id_incarico.", 1 ";

$query=$query.");";

//echo $query."<br>";
//exit;
$result=pg_query($conn, $query);


if ($id!=''){
	$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(	id_segnalazione_in_lavorazione, log_aggiornamento";
	
	//values
	$query=$query.") VALUES (".$id.", ' Assegnato nuovo incarico alla seguente unità operativa: ".$uo_descrizione." - <a class=\"btn btn-info\" href=\"dettagli_incarico.php?id=".$id_incarico."\"> Visualizza dettagli </a>'";
	
	$query=$query.");";
	
	//echo $query;
	//exit;
	$result=pg_query($conn, $query);
}

$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Inviato incarico ".$id_incarico."');";
$result = pg_query($conn, $query_log);


//**********************************************************
// telegram

/*
require('../token_telegram.php');

require('../send_message_telegram.php');


$query_telegram="SELECT telegram_id from users.utenti_sistema where telegram_id !='' and telegram_attivo='t';";
echo $query_telegram;
echo "<br>";
$messaggio="E ' stato creato un nuovo evento, consultare il programma ".$link." ";
echo $messaggio;
echo "<br>";
$result_telegram = pg_query($conn, $query_telegram);
while($r_telegram = pg_fetch_assoc($result_telegram)) {
	sendMessage($r_telegram['telegram_id'], $messaggio , $token);
}

*/



//**********************************************************


//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
//echo $query_log;
require('../token_telegram.php');

require('../send_message_telegram.php');

$query="SELECT mail, id_telegram FROM users.t_mail_incarichi WHERE cod=$1;";
$result = pg_prepare($conn, "myquery0", $query);
$result = pg_execute($conn, "myquery0", array($uo));
$mails=array();
$telegram=array();
$messaggio="\xE2\x80\xBC E' stato assegnato un nuovo incarico sulla segnalazione n. ".$segn." all'unità operativa di tua appartenenza ".$uo_descrizione." con i seguenti dettagli:".$descrizione."\n";
$messaggio= $messaggio ." Visualizzare i dettagli dell'incarico accedendo con le tue credenziali al Sistema di Gestione delle Emergenze del Comune di Genova.";
while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
  array_push($telegram,$r['id_telegram']);
  //sendMessage($r['id_telegram'], $messaggio , $token);
}
foreach ($telegram as $chatid) {
	sendMessage($chatid, $messaggio , $token);
  }

echo "<br>";
//echo $query;
//echo "<br>";
echo "<br>".count($mails). " mail registrate a sistema</h3>";

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
$mail->Subject = 'Urgente - Nuovo incarico dalla Protezione Civile del Comune di Genova';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  'Hai ricevuto questo messaggio in quanto 衳tato assegnato un nuovo incarico alla seguente Unitࠠoperativa 
 '.$uo_descrizione.'. <br> Ti preghiamo di non rispondere a questa mail, ma di visualizzare i dettagli dell\'incarico accedendo 
 con le tue credenziali al nuovo <a href="https://emergenze.comune.genova.it/emergenze/pages/dettagli_incarico.php?id='.$id_incarico.'" " > Sistema di Gestione delle Emergenze </a> del Comune di Genova.
 <br> <br> Protezione Civile del Comune di Genova. <br><br>--<br> Ricevi questa mail  in quanto il tuo indirizzo mail 衲egistrato a sistema. 
 Per modificare queste impostazioni 衰ossibile inviare una mail a salaemergenzepc@comune.genova.it inoltrando il presente messaggio. Ti ringraziamo per la preziosa collaborazione.';


  
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
    //echo "<h3>Problema nell'invio della mail: " . $mail->ErrorInfo;
   //echo "<h3>Problema nell'invio della mail";
   echo '<div style="text-align: center;"><img src="../../img/no_mail.png" width="75%" alt=""></div>';
	//echo '<br>L\'incarico &egrave stato correttamente assegnato, ma si &egrave riscontrato un problema nell\'invio della mail.';
	echo '<br><h1>Entro 15" verrai re-indirizzato alla pagina della tua segnalazione, clicca al seguente ';
	
	if ($id!=''){
    	echo '<a href="../dettagli_segnalazione.php?id='.$segn.'">link</a> per saltare l\'attesa.</h1>' ;
    } else {
    	echo '<a href="../dettagli_provvedimento_cautelare.php?id='.$id_pc.'">link</a> per saltare l\'attesa.</h1>' ;
    }
	
	//sleep(30);
	if ($id!=''){
    	header("refresh:12;url=../dettagli_segnalazione.php?id=".$segn);
    } else {
    	header("refresh:12;url=../dettagli_provvedimento_cautelare.php?id=".$id_pc);
    }
} else {
    echo "Message sent!";
	if ($id!=''){
    	header("location:../dettagli_segnalazione.php?id=".$segn);
    } else {
    	header("location:../dettagli_provvedimento_cautelare.php?id=".$id_pc);
    }
}



//exit;
//header("location: ../dettagli_segnalazione.php?id=".$segn);


?>
