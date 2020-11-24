<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$address=$_POST["address"];

echo $address."<br>";

$mails=array($address);

echo $mails."<br>";

//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //segnazione in lavorazione


$descrizione= str_replace("'", "''", $_POST["descrizione"]);

$note= str_replace("'", "''", $_POST["note"]);


//$uo= str_replace("'", "", $_GET["uo"]);

$uo=$_POST["uo"];

$id_uo=$_POST["id_uo"];

$lat=$_POST["lat"];
$lon=$_POST["lon"];

echo "Id_uo:".$id_uo. "<br>";
echo "Unita_operativa:".$uo. "<br>";


echo "Descrizione:".$descrizione. "<br>";

echo "Altre note:".$note. "<br>";
//echo "<h2>La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2> <br> ";



echo "<br>";

//echo $query_log;

//echo $query;






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
$mail->Subject = 'Urgente - La tua squadra ha ricevuto un nuovo incarico ('.$uo.')';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->Body =  'Questa mail ti viene inviata tramite il nuovo sistema informativo del Comune di Genova
$body =  'Questa mail ti viene inviata tramite il nuovo sistema informativo del Comune di Genova
per le gestione delle emergenze per descrivere un incarico assegnato alla tua squadra che risulta di competenza 
dell\'unità operativa '.$uo.'  
  <br>Ti preghiamo di non rispondere a questa mail
  <br> - Descrizione incarico assegnato alla tua Unità: '.$descrizione.'
  <br> - Eventuali altre note della tua Unità: '.$note.'
  
  <br><br>
  Visualizza la posizione di massima della segnalazione su <a href="https://maps.google.com/?q='.$lat.','.$lon.'" >
  google maps</a> (anche tramite navigatore Android) 
  
  <br><br>
  Il COC tramite mail della Protezione Civile - Comune Genova';
  
require('../informativa_privacy_mail.php');

$mail-> Body=$body ;


// metto in CC i contatti dell'Unità operativa
$query="SELECT mail FROM users.t_mail_incarichi WHERE cod='".$id_uo."';";
echo $query;

$result=pg_query($conn, $query);

while($r = pg_fetch_assoc($result)) {
	$mail->AddCC($r[mail]);
  
}



//allegati eventuali
if (isset($_FILES['userfile']) &&
    $_FILES['userfile']['error'] == UPLOAD_ERR_OK) {
    $mail->AddAttachment($_FILES['userfile']['tmp_name'],
                         $_FILES['userfile']['name']);

}


//$mail->Body =  'Corpo del messaggio';
//$mail->msgHTML(file_get_contents('E\' arrivato un nuovo incarico da parte del Comune di Genova. Visualizza lo stato dell\'incarico al seguente link e aggiornalo quanto prima. <br> Ti chiediamo di non rispondere a questa mail'), __DIR__);
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
//$mail->addAttachment('images/phpmailer_mini.png');
//send the message, check for errors
//echo "<br>OK 2<br>";
if (!$mail->send()) {
    echo "<h3>Problema nell'invio della mail: " . $mail->ErrorInfo;
	?>
	<script> alert(<?php echo "Problema nell'invio della mail: " . $mail->ErrorInfo;?>) </script>
	<?php
	echo '<br>L\'incarico è stato correttamente assegnato, ma si è riscontrato un problema nell\'invio della mail.';
	echo '<br>Entro 10" verrai re-indirizzato alla pagina della tua segnalazione, clicca al seguente ';
	echo '<a href="../dettagli_incarico.php?id='.$id.'">link</a> per saltare l\'attesa.</h3>' ;
	//sleep(30);
   header("refresh:30;url=../dettagli_incarico.php?id=".$id);
} else {
    echo "Message sent!";
	header("location: ../dettagli_incarico.php?id=".$id);
}



//exit;
//header("location: ../dettagli_segnalazione.php?id=".$segn);


?>
