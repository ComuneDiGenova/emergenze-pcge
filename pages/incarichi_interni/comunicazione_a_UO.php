<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];
$allegato_array='';
include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

//echo "<h2> La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2>";


//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //incarico


$id_lavorazione=$_POST["id_lavorazione"];

$note= str_replace("'", "''", $_POST["note"]);
$uo=$_POST["uo"];

$id_evento=$_POST["id_evento"];


//echo "Incarico:".$id. "<br>";
//echo "Note:".$note. "<br>";
//echo "UO:".$uo. "<br>";
//echo "Id evento:".$id_evento. "<br>";
//exit;



// Count total files
$countfiles = count(array_filter($_FILES['userfile_i']['name']));
//echo $countfiles;
//exit;
 
 // Looping all files
 for($i=0;$i<$countfiles;$i++){
   $filename = $_FILES['userfile_i']['name'][$i];
   
   // Upload file (example from internet)
   //move_uploaded_file($_FILES['file']['tmp_name'][$i],'upload/'.$filename);


// per prima cosa verifico che il file sia stato effettivamente caricato
/*if (!isset($_FILES['userfile_i']) || !is_uploaded_file($_FILES['userfile_i']['tmp_name'])) {
  echo 'Non hai inviato nessun file...';    
} else {*/

	//percorso della cartella dove mettere i file caricati dagli utenti


	$uploaddir0="../../../emergenze_uploads/";

	$uploaddir1= $uploaddir0. "e_".$id_evento."/";

	if (file_exists($uploaddir1)) {
		//echo "The file $uploaddir1 exists <br>";
		echo " ";
	} else {
		//echo "The file $uploaddir1 does not exist <br>";
		$crea_folder="mkdir ".$uploaddir1;
		exec($crea_folder);
	}

	$uploaddir= $uploaddir1. "ii_".$id."/";

	if (file_exists($uploaddir)) {
		//echo "The file $uploaddir exists <br>";
		echo " ";
	} else {
		//echo "The file $uploaddir does not exist <br>";
		$crea_folder="mkdir ".$uploaddir;
		exec($crea_folder);
	}

	//Recupero il percorso temporaneo del file
	$userfile_tmp = $_FILES['userfile_i']['tmp_name'][$i];

	//recupero il nome originale del file caricato e tolgo gli spazi
	//$userfile_name = $_FILES['userfile_i']['name'];
	$userfile_name = preg_replace("/[^a-z0-9\_\-\.]/i", '', basename($_FILES['userfile_i']["name"][$i]));


	$datafile=date("YmdHis");
	$allegato=$uploaddir .$datafile."_". $userfile_name;

	//echo $allegato."<br>";

	//copio il file dalla sua posizione temporanea alla mia cartella upload
	if (move_uploaded_file($userfile_tmp, $allegato)) {
	  //Se l'operazione è andata a buon fine...
	  //echo 'File inviato con successo.';
	}else{
	  //Se l'operazione è fallta...
	  echo 'Upload NON valido!'; 
	}


	$allegato=str_replace("../../../", "", $allegato); //allegato database
	if ($allegato_array==''){
		$allegato_array=$allegato;
	} else {
		$allegato_array=$allegato_array .";". $allegato;
	}
}

//exit;

$query= "INSERT INTO segnalazioni.t_comunicazioni_incarichi_interni_inviate(id_incarico, testo";
if (isset($allegato)){
	$query= $query . ", allegato";
}
$query= $query .")VALUES (".$id.", '".$note."'";
if (isset($allegato)){
	$query= $query . ",'". $allegato_array."'";
}
$query= $query .");";




//echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
//echo "Result:". $result."<br>";



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Inviata comunicazione a PC (incarico interno ".$id.")');";
//echo $query_log."<br>";
$result = pg_query($conn, $query_log);


//echo "Result:". $result."<br>";


echo "<br>";
//echo $query_log;


//****************************************************************************
//			Invio mail
//****************************************************************************



$query="SELECT mail FROM users.t_mail_squadre WHERE cod='".$uo."';";
//echo $query;
//exit;
$result=pg_query($conn, $query);
$mails=array();
while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
}

echo "<br>";
//echo $query;
//echo "<br>";
//echo count($mails). " registrate a sistema";

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
$mail->Subject = 'Urgente - Nuovo messaggio dalla Protezione Civile del Comune di Genova';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  'La Protezione Civile di Genova ti ha inviato un nuovo messaggio ("'.$note.'") a proposito dell\'incarico interno assegnato. 
<br> Ti preghiamo di non rispondere a questa mail, ma di visualizzare i dettagli dell\'incarico accedendo 
 con le tue credenziali alla <a href="https://emergenze.comune.genova.it/emergenze/pages/dettagli_incarico.php?id='.$id.'" > pagina
 </a> del nuovo Sistema di Gestione delle Emergenze  del Comune di Genova.
 <br> <br> Protezione Civile del Comune di Genova. <br><br>--<br> Ricevi questa mail  in quanto il tuo indirizzo mail è registrato a sistema. 
 Per modificare queste impostazioni è possibile inviare una mail a salaemergenzepc@comune.genova.it ';

  
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
	?>
	<!--script> alert(<?php echo "Problema nell'invio della mail: " . $mail->ErrorInfo;?>) </script-->
	<?php
	echo '<div style="text-align: center;"><img src="../../img/no_mail_com.png" width="75%" alt=""></div>';
	echo '<br><h1>Entro 10" verrai re-indirizzato alla pagina della tua segnalazione, clicca al seguente ';
	//echo '<br>La comunicazione è stata correttamente inserita a sistema, ma si è riscontrato un problema nell\'invio della mail.';
	echo '<a href="../dettagli_incarico_interno.php?id='.$id.'">link</a> per saltare l\'attesa.</h1>' ;
	//sleep(30);
    header("refresh:10;url=../dettagli_incarico_interno.php?id=".$id);
} else {
    echo "Message sent!";
	header("location: ../dettagli_incarico_interno.php?id=".$id);
}
//exit;
//header("location: ../dettagli_incarico.php?id=".$id);


?>