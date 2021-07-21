<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

require('../check_evento.php');



//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //segnazione in lavorazione

$segn=str_replace("'", "", $_GET['s']); //segnazione in lavorazione

$descrizione= str_replace("'", "''", $_POST["descrizione"]);

$uo= str_replace("'", "''", $_POST["uo"]);


//echo "Segnalazione in lavorazione:".$id. "<br>";
//echo "Segnalazione:".$segn. "<br>";
//echo "Descrizione:".$descrizione. "<br>";
//echo "Squadra:".$uo. "<br>";



//echo "<h2>La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2> <br> ";




$query_max= "SELECT max(id) FROM segnalazioni.t_incarichi_interni;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_incarico=$r_max["max"]+1;
	} else {
		$id_incarico=1;	
	}
}
//echo "Id incarico interno:".$id_incarico. "<br>";



// recupero info su U.O. 

$query_uo= "SELECT * FROM users.v_squadre where id='".$uo."';";


$result_uo = pg_query($conn, $query_uo);
while($r_uo = pg_fetch_assoc($result_uo)) {
	$uo_descrizione=$r_uo['nome'];
}
//echo $query_uo."<br>";

//echo "Descrizione uo:".$uo_descrizione. "<br>";


$query="UPDATE users.t_squadre SET id_stato=1 WHERE id=".$uo.";";
//echo $query;
//exit;
$result=pg_query($conn, $query);



$query= "INSERT INTO segnalazioni.t_incarichi_interni ( id, descrizione, id_profilo, id_squadra";

//values
$query=$query.") VALUES (".$id_incarico.", '".$descrizione."', '".$profilo_ok."', '".$uo."' ";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);





echo "<br>";
$query= "INSERT INTO segnalazioni.join_segnalazioni_incarichi_interni(id_incarico, id_segnalazione_in_lavorazione";

//values
$query=$query.") VALUES (".$id_incarico.", ".$id." ";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);


$query= "INSERT INTO segnalazioni.stato_incarichi_interni(id_incarico, id_stato_incarico";

//values
$query=$query.") VALUES (".$id_incarico.", 1 ";

$query=$query.");";

//echo $query."<br>";
//exit;
$result=pg_query($conn, $query);



$query= "INSERT INTO segnalazioni.join_incarichi_interni_squadra (id_incarico,id_squadra ";
$query=$query.") VALUES (".$id_incarico.",".$uo.");";
//echo $query;
//exit;
$result=pg_query($conn, $query);




$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(	id_segnalazione_in_lavorazione, log_aggiornamento";

//values
$query=$query.") VALUES (".$id.", ' Assegnato nuovo incarico interno alla seguente squadra: ".$uo_descrizione." - <a class=\"btn btn-info\" href=\"dettagli_incarico_interno.php?id=".$id_incarico."\"> Visualizza dettagli </a>'";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Inviato incarico interno ".$id_incarico."');";
$result = pg_query($conn, $query_log);




//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
//echo $query_log;

require('../token_telegram.php');

require('../send_message_telegram.php');

/* $query="SELECT mail FROM users.t_mail_squadre WHERE cod='".$uo."';";
$result=pg_query($conn, $query);
$mails=array();
while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
} */
$query="SELECT mail, telegram_id, u.telegram_attivo
	FROM users.t_mail_squadre s
	left join users.v_utenti_sistema u 
  	on s.matricola_cf = u.matricola_cf 
	WHERE cod=$1;";
$result = pg_prepare($conn, "myquery0", $query);
$result = pg_execute($conn, "myquery0", array($uo));
$mails=array();
//$telegram_id=array();
$messaggio="\xE2\x80\xBC E' stato assegnato un nuovo incarico interno sulla segnalazione n. ".$segn." alla squadra di tua appartenenza ".$uo_descrizione." con i seguenti dettagli: ".$descrizione."\n";
$messaggio= $messaggio ." \xF0\x9F\x91\x8D per accettare l'incarico digita /accetto \xE2\x9D\x8C per rifiutare l'incarico digita /rifiuto";


while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
  //array_push($telegram_id,$r['telegram_id']);
  if($r['telegram_id']!='' && $r['telegram_attivo']=='t'){
	sendMessage($r['telegram_id'], $messaggio , $token);
	$query2 = "SELECT ST_X(geom) as lon, ST_Y(geom) as lat FROM segnalazioni.t_segnalazioni where id=$1;";
	$result2 = pg_prepare($conn, "myquery1", $query2);
	$result2 = pg_execute($conn, "myquery1", array($segn));
	while($r2 = pg_fetch_assoc($result2)) {
	 	// $coord = 'lat = '.$r2['lat'].' lon = '.$r2['lon'];
		// sendMessage($r['telegram_id'], $coord , $token);
	 	sendLocation($r['telegram_id'], $r2['lat'], $r2['lon'], $token);
	// 	//$map= 'https://api.telegram.org/'.$token.'/sendlocation?chat_id='.$r['telegram_id'].'&latitude='.$r2['lat'].'&longitude='.$r2['lon'].''
	}
  }
}


echo "<br>";
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
$mail->Subject = 'Urgente - Nuovo incarico interno assegnato tramite il Sistema di Gestione Emergenze del Comune di Genova';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  'Hai ricevuto questo messaggio in quanto � stato assegnato un nuovo incarico interno alla squadra di tua appartenenza 
 '.$uo_descrizione.'. <br> Ti preghiamo di non rispondere a questa mail, ma di visualizzare i dettagli dell\'incarico accedendo 
 con le tue credenziali al nuovo <a href="https://emergenze.comune.genova.it/emergenze/pages/dettagli_incarico_interno.php?id='.$id_incarico.'" > Sistema di Gestione delle Emergenze </a> del Comune di Genova.
 <br> <br> Protezione Civile del Comune di Genova. <br><br>--<br> Ricevi questa mail  in quanto il tuo indirizzo mail � registrato a sistema. 
 Per modificare queste impostazioni � possibile inviare una mail a salaemergenzepc@comune.genova.it ';

  
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
    echo "<h3>Problema nell'invio della mail";
	?>
	<!--script> alert(<?php echo "Problema nell'invio della mail: " . $mail->ErrorInfo;?>) </script-->
	<?php
	//echo '<br>L\'incarico &egrave stato correttamente assegnato, ma si &egrave riscontrato un problema nell\'invio della mail.';
   echo '<div style="text-align: center;"><img src="../../img/no_mail.png" width="75%" alt=""></div>';
   echo '<br><h1>Entro 15" verrai re-indirizzato alla pagina della tua segnalazione, clicca al seguente ';
	echo '<a href="../dettagli_segnalazione.php?id='.$segn.'">link</a> per saltare l\'attesa.</h1>' ;
	//sleep(30);
    header("refresh:12;url=../dettagli_segnalazione.php?id=".$segn);
} else {
    echo "Message sent!";
	header("location: ../dettagli_segnalazione.php?id=".$segn);
}



//exit;
//header("location: ../dettagli_segnalazione.php?id=".$segn);


?>
