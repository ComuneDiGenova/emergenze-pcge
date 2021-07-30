<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

$id=$_GET["id"];
//$id=str_replace("'", "", $id);


$data_inizio=$_POST["data_inizio"].' '.$_POST["hh_start"].':'.$_POST["mm_start"];

$data_mail= 'delle '.$_POST["hh_start"].':'.$_POST["mm_start"].' del '.$_POST["data_inizio"];
//$d1 = new DateTime($data_inizio);
//$d2 = new DateTime($data_fine);
$d1 =  strtotime($data_inizio);

$aggiornamento= str_replace("'", "''", $_POST["aggiornamento"]);

$hh=$_SERVER['HTTP_X_FORWARDED_HOST'];


//$d1 = DateTime::createFromFormat('Y-m-d H:M', strtotime($data_inizio));
//$d2 = DateTime::createFromFormat('Y-m-d H:M', $data_fine);
// echo $data_inizio;
// echo "<br>";
// echo $d1;
// echo "<br>";



// Count total files
 $countfiles = count(array_filter($_FILES['userfile_i']['name']));



 // Looping all files
 for($i=0;$i<$countfiles;$i++){
   $filename = $_FILES['userfile_i']['name'][$i];
   
	//percorso della cartella dove mettere i file caricati dagli utenti
	$uploaddir0="../../../emergenze_uploads/";

	$uploaddir1= $uploaddir0. "e_".$id."/";

	if (file_exists($uploaddir1)) {
		//echo "The file $uploaddir1 exists <br>";
		echo "";
	} else {
		//echo "The file $uploaddir1 does not exist <br>";
		$crea_folder="mkdir ".$uploaddir1;
		exec($crea_folder);
	}

	$uploaddir= $uploaddir1. "monitoraggio_meteo/";

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

	echo $allegato."<br>";

	//copio il file dalla sua posizione temporanea alla mia cartella upload
	if (move_uploaded_file($userfile_tmp, $allegato)) {
	  //Se l'operazione è andata a buon fine...
	  //echo 'File inviato con successo.';
	  echo ' ';
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




$query= "INSERT INTO segnalazioni.t_comunicazioni_segnalazioni(id_lavorazione, mittente, testo";
if ($allegato!=''){
	$query= $query . ", allegato";
}
$query= $query .")VALUES (".$id_lavorazione.", '".$mittente."', '".$note."'";
if ($allegato!=''){
	$query= $query . ",'". $allegato_array."'";
}
$query= $query .");";




$query="INSERT INTO report.t_aggiornamento_meteo (id_evento,data,aggiornamento";
if ($allegato!=''){
	$query= $query . ", allegati";
}
$query= $query .") VALUES";
$query= $query." ('".$id."','".$data_inizio."','".$aggiornamento."'";
if ($allegato!=''){
	$query= $query . ",'". $allegato_array."'";
}
$query= $query.");";
//echo $query;
//exit;
$result = pg_query($conn, $query);
//echo "<br>";





//exit;



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('report','".$operatore ."', 'Inserito aggiornamento meteo (evento ".$id."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
//echo "<br>";
//echo $query_log;


//**********************************************************
// telegram

require('../token_telegram.php');

require('../send_message_telegram.php');


$query_telegram="SELECT telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';";
echo $query_telegram;
echo "<br>";
$messaggio= "\xE2\x9A\xA0 - MONITORAGGIO METEO \xE2\x9A\xA0 \n\n Aggiornamento: ". $aggiornamento;
$messaggio= $messaggio. "\n\nPer maggiori info consultre il programma ".$link." ";
$messaggio= $messaggio ." (\xE2\x84\xB9 ricevi questo messaggio in quanto operatore di Protezione Civile \xE2\x84\xB9)";
echo $messaggio;
echo "<br>";
$result_telegram = pg_query($conn, $query_telegram);
while($r_telegram = pg_fetch_assoc($result_telegram)) {
	sendMessage($r_telegram['telegram_id'], $messaggio , $token);
	if ($allegato!=''){
	$allegati=explode(";",$allegato_array);
	// Count total files
	$countfiles2 = count($allegati);
	//echo $countfiles2;
	//echo "<br>";
	// Looping all files
	if($countfiles2 > 0) {
		for($i=0;$i<$countfiles2;$i++){
			$n_a=$i+1;
			//echo "../../../".$allegati[$i];
			//echo "<br>";
			//$hh=$_SERVER['HTTP_X_FORWARDED_HOST'];
			//echo "<br>";
			//$img = curl_file_create('test.png','image/png');
			//sendPhoto2($r_telegram['telegram_id'], 'https://'.$hh.'/'.$allegati[$i].'\'' , $token);
			sendPhoto($r_telegram['telegram_id'], '../../../'.$allegati[$i] , $token);
		}
	}
}
	
}
//exit;





//**********************************************************


//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
//echo "<br>";
//echo $query_log;

$query="SELECT mail FROM users.t_mail_meteo WHERE valido = 't' ;";
$result=pg_query($conn, $query);
$mails=array();
while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
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
require '../incarichi/credenziali_mail.php';


//Set who the message is to be sent from
$mail->setFrom('monitoraggiometeo@comune.genova.it', 'Monitoraggio meteo- Protezione Civile Comune di Genova');
//Set an alternative reply-to address
$mail->addReplyTo('no-reply@comune.genova.it', 'No Reply');
//Set who the message is to be sent to

//$mails=array('vobbo@libero.it','roberto.marzocchi@gter.it');
while (list ($key, $val) = each ($mails)) {
  //$mail->AddAddress($val);
  $mail->AddBCC($val);
}
//Set the subject line
$mail->Subject = 'Nuovo aggiornamento meteo ' .$data_mail.' '.$note_ambiente_mail.'';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  $aggiornamento . ' <br> <br> Protezione Civile del Comune di Genova. '.$hh.'<br><br>--<br><br>
							Aggiornamenti meteo per l\'evento '.$id.'<br><br>';

$query="SELECT * FROM report.t_aggiornamento_meteo WHERE id_evento = $1 ;";
$result = pg_prepare($conn, "myquery0", $query);
$result = pg_execute($conn, "myquery0", array($id));
while($r = pg_fetch_assoc($result)) {
	$body .= $r['data'].' - '.$r['aggiornamento'].'<br><br>';
	if($r['allegati']!==null){
		if (strpos($r['allegati'], ';') !== false) { 
			$allegati=explode(";",$r['allegati']);
			$countfiles3 = count($allegati);
			// Looping all files
			if($countfiles3 > 0) {
				for($i=0;$i<$countfiles3;$i++){
					$n_a=$i+1;
					$body .= '<a href="https://'.$hh.'/'.$allegati[$i].'" target="_blank">Visualizza allegato '.$n_a.'</a> <br><br>';
					//$body .= '<a href="https://emergenze.comune.genova.it/'.$allegati[$i].'" target="_blank">Visualizza allegato '.$n_a.'</a> <br><br>';
				}
			}
		}else{
			$body .= '<a href="https://'.$hh.'/'.$r['allegati'].'" target="_blank">Visualizza allegato</a> <br><br>';
			//$body .= '<a href="https://emergenze.comune.genova.it/'.$r['allegati'].'" target="_blank">Visualizza allegato</a> <br><br>';
		}
	}
}

$body .=  '<br><br>--<br> Ricevi questa mail  in quanto il tuo indirizzo mail è registrato a sistema (invio monitoraggio meteo). 
 Per modificare queste impostazioni è possibile inviare una mail a adminemergenzepc@comune.genova.it inoltrando il presente messaggio. Ti ringraziamo per la preziosa collaborazione.';

require('../informativa_privacy_mail.php');

$mail-> Body=$body ;

$mail->addBCC("assistenzagis@gter.it", "Copia per conoscenza");

if ($allegato!=''){
	$allegati=explode(";",$allegato_array);
	// Count total files
	$countfiles2 = count($allegati);
	echo $countfiles;
	// Looping all files
	if($countfiles2 > 0) {
		for($i=0;$i<$countfiles2;$i++){
			$n_a=$i+1;
			echo $allegati[$i];
			$mail->addAttachment("../../../".$allegati[$i]);
		}
	}
}
//exit;

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
//header("location: ../reportistica.php");
header('Location: ' . $_SERVER['HTTP_REFERER']);


?>