<?php

session_start();

//echo $_SESSION['user'];

include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';

require('../check_evento.php');

if($profilo_sistema==8 and $uo_inc=='uo_1' ){
	$profilo_ok=3;
}

//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //segnazione in lavorazione

$segn=str_replace("'", "", $_GET['s']); //segnazione in lavorazione

$descrizione= str_replace("'", "''", $_POST["descrizione"]);

$uo= str_replace("'", "''", $_POST["uo"]);

$id_evento= str_replace("'", "''", $_POST["evento"]);

//echo "Segnalazione in lavorazione:".$id. "<br>";
//echo "Segnalazione:".$segn. "<br>";
//echo "Descrizione:".$descrizione. "<br>";
//echo "Squadra:".$uo. "<br>";



//echo "<h2>La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2> <br> ";




$query_max= "SELECT max(id) FROM segnalazioni.t_sopralluoghi;";
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



/*$query_g= "SELECT geom FROM segnalazioni.v_segnalazioni where id='".$segn."';";
$result_g = pg_query($conn, $query_g);
while($r_g = pg_fetch_assoc($result_g)) {
	$geom=$r_g['geom'];
}
echo $query_g."<br>";
*/



//echo "Descrizione uo:".$uo_descrizione. "<br>";





//echo "id_civico: ". $_POST["id_civico"];
//echo "Latitudine: ". $_POST["lat"];

echo "<br>";

if ($_POST["id_civico"]!=''){
 	$query_civico= 'SELECT st_transform (geom,4326) as geom FROM geodb.civici where id='.$_POST["id_civico"].';';
 	//echo $query_civico;
 	echo "<br>";
 	// se ci fossero problemi con il valore 'geom' controlla l record corrispondente nella tabella geodb.m_tables, 
	// che gestisce il trasferimento dati da Oracle a postgis
 	$result_civico=pg_query($conn, $query_civico);
	while($rc = pg_fetch_assoc($result_civico)) {
		//
		$geom="'".$rc["geom"]."'"; // messo fra apici per poi includerlo nella successiva query	
	}
} else if($_POST["lat"]!='') {
	// geometria su mappa o con coordinate
	$geom="ST_GeomFromText('POINT(".$_POST["lon"]." ".$_POST["lat"].")',4326)";
} else {
	echo "ERROR: geometria non definita<br>";
	exit;
}




$query="UPDATE users.t_squadre SET id_stato=1 WHERE id=".$uo.";";
//echo $query;
//exit;
$result=pg_query($conn, $query);



$query= "INSERT INTO segnalazioni.t_sopralluoghi ( id, descrizione, id_profilo, id_evento, geom";

//values
$query=$query.") VALUES (".$id_sopralluogo.", '".$descrizione."', '".$profilo_ok."',  ". $id_evento. ", ". $geom." ";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);



echo "<br>";


$query= "INSERT INTO segnalazioni.join_sopralluoghi_squadra (id_sopralluogo,id_squadra ";
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


if($_POST["permanente"]=='on') {
	$query="UPDATE segnalazioni.t_sopralluoghi SET time_preview=now(), time_start=now() WHERE id=".$id_sopralluogo.";";
	$result=pg_query($conn, $query);
	$query= "INSERT INTO segnalazioni.stato_sopralluoghi(id_sopralluogo, id_stato_sopralluogo";

	//values
	$query=$query.") VALUES (".$id_sopralluogo.", 2 ";

	$query=$query.");";

	//echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);

} else {
	$query= "INSERT INTO segnalazioni.stato_sopralluoghi(id_sopralluogo, id_stato_sopralluogo";
	//values
	$query=$query.") VALUES (".$id_sopralluogo.", 1 ";
	$query=$query.");";
	//echo $query."<br>";
	//exit;
	$result=pg_query($conn, $query);
}




/*$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione( id_segnalazione_in_lavorazione, log_aggiornamento";

//values
$query=$query.") VALUES (".$id.", ' Assegnato nuovo sopralluogo alla seguente squadra: ".$uo_descrizione." - <a class=\"btn btn-info\" href=\"dettagli_sopralluogo.php?id=".$id_sopralluogo."\"> Visualizza dettagli </a>'";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);*/


$query_log= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Inviato presidio/sopralluogo ".$id_sopralluogo."');";
$result = pg_query($conn, $query_log);




//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
//echo $query_log;

$query="SELECT mail FROM users.t_mail_squadre WHERE cod='".$uo."';";
$result=pg_query($conn, $query);
$mails=array();
while($r = pg_fetch_assoc($result)) {
  array_push($mails,$r['mail']);
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
$mail->Subject = 'Urgente - Nuovo presidio assegnato tramite il Sistema di Gestione Emergenze del Comune di Genova';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  'Hai ricevuto questo messaggio in quanto � stato assegnato un nuovo presidio alla squadra di tua appartenenza 
 '.$uo_descrizione.'. <br> Ti preghiamo di non rispondere a questa mail, ma di visualizzare i dettagli del presidio accedendo 
 con le tue credenziali al nuovo <a href="http://192.168.153.110/emergenze/pages/dettagli_sopralluogo.php?id='.$id_sopralluogo.'" > Sistema di Gestione delle Emergenze </a> del Comune di Genova.
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
	echo '<br><h1>Entro 15" verrai re-indirizzato alla pagina della tua segnalazione, clicca al seguente ';
	echo '<a href="../dettagli_sopralluogo.php?id='.$id_sopralluogo.'">link</a> per saltare l\'attesa.</h1>' ;
	//sleep(30);
    header("refresh:12;url=../dettagli_sopralluogo.php?id=".$id_sopralluogo);
} else {
    echo "Message sent!";
	header("location: ../dettagli_sopralluogo.php?id=".$id_sopralluogo);
}



//exit;
//header("location: ../dettagli_segnalazione.php?id=".$segn);


?>
