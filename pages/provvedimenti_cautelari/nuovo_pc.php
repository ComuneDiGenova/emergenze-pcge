<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

require('../check_evento.php');
require ('../note_ambiente.php');



//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //segnazione in lavorazione

$segn=str_replace("'", "", $_GET['s']); //segnazione in lavorazione

$descrizione= str_replace("'", "''", $_POST["descrizione"]);

$uo= str_replace("'", "''", $_POST["uo"]);

$id_evento=$_POST["evento"];

$codvia=$_POST["codvia"];

$id_civico1=explode(',',$_POST["id_civico1"])[2];
$id_civico2=explode(',',$_POST["id_civico2"])[2];


$desc_via= str_replace("'", "''", $_POST["desc_via"]);


//$profilo_sistema=$_POST["id_sistema"];



//echo "Segnalazione in lavorazione:".$id. "<br>";
//echo "Segnalazione:".$segn. "<br>";
//echo "Descrizione:".$descrizione. "<br>";
//echo "Squadra:".$uo. "<br>";
//echo "Id evento:".$id_evento. "<br>";

//echo "codvia:".$codvia. "<br>";
//echo "id_civico1:".$id_civico1. "<br>";
//echo "id_civico2:".$id_civico2. "<br>";





$tipo_pc = $_POST['tipo_pc'];

$nome_tabella_oggetto_rischio = $_POST['nome_tabella_oggetto_rischio'];
$descrizione_oggetto_rischio = $_POST['descrizione_oggetto_rischio'];
$nome_campo_id_oggetto_rischio = $_POST['nome_campo_id_oggetto_rischio'];

if($_POST['id_oggetto_rischio']!='') {
	$id_oggetto_rischio = $_POST['id_oggetto_rischio'];
} else if($_POST['id_civico']!='') {
	$id_oggetto_rischio = $_POST['id_civico'];
} else if($_POST['id_sottopasso']!='') {
	$id_oggetto_rischio = $_POST['id_sottopasso'];
} else if($_POST['codvia']!='') {
	$id_oggetto_rischio = $_POST['codvia'];
} else {
	echo "ERRORE: Non è specificato l'ID dell'oggetto a rischio";
	exit;
}


//echo "tipo_pc: " .$tipo_pc. "<br>";
//echo "nome_tabella_oggetto_rischio: " .$nome_tabella_oggetto_rischio. "<br>";
//echo "descrizione_oggetto_rischio: " .$descrizione_oggetto_rischio. "<br>";
//echo "nome_campo_id_oggetto_rischio: " .$nome_campo_id_oggetto_rischio. "<br>";
//echo "id_oggetto_rischio: " .$id_oggetto_rischio. "<br>";



//echo "<h2>La gestione dei provvedimenti cautelari e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2> <br> ";


/*

TAGLIA LA STRADA DA PUNTO A PUNTO
*/

$query=" SELECT ST_LineLocatePoint(ST_LineMerge(ST_SnapToGrid(v.geom,1)), st_closestpoint(v.geom, c.geom)) as punto1
FROM geodb.v_vie_unite v, geodb.civici c
WHERE  v.codvia='".$codvia."' and c.id='".$id_civico1."';";

$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$punto1=$r['punto1'];
}
//echo $query;
//exit;
echo "<br>";

$query=" SELECT ST_LineLocatePoint(ST_LineMerge(ST_SnapToGrid(v.geom,1)), st_closestpoint(v.geom, c.geom)) as punto2
FROM geodb.v_vie_unite v, geodb.civici c
WHERE  v.codvia='".$codvia."' and c.id='".$id_civico2."';";

$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$punto2=$r['punto2'];
}

//echo $query;
//exit;
echo "<br>";


//echo "Punto1:" .$punto1."<br>";

//echo "Punto2:" .$punto2."<br>";

if ($punto1=='' or $punto2 ==''){
	$query=" SELECT testo FROM geodb.civici WHERE id='".$id_civico1."';";
	$result = pg_query($conn, $query);
	while($r = pg_fetch_assoc($result)) {
		$civico1=$r['testo'];
	}
	
	$query=" SELECT testo FROM geodb.civici WHERE id='".$id_civico2."';";
	$result = pg_query($conn, $query);
	while($r = pg_fetch_assoc($result)) {
		$civico2=$r['testo'];
	}
	
	$desc_via= "(geometria non individuabile su mappa)";
}

//echo "Desc via:" .$desc_via."<br>";

if ($punto1 < $punto2) {
	$query="SELECT ST_transform(ST_LineMerge(ST_LineSubstring (
  ST_LineMerge(ST_SnapToGrid(v.geom,1)),
  ST_LineLocatePoint(ST_LineMerge(ST_SnapToGrid(v.geom,1)), st_closestpoint(v.geom, c.geom)),
  ST_LineLocatePoint(ST_LineMerge(ST_SnapToGrid(v.geom,1)), st_closestpoint(v.geom, cc.geom))
  )),4326) 
as geom
FROM geodb.v_vie_unite v, geodb.civici c, geodb.civici cc
WHERE  v.codvia='".$codvia."' and c.id='".$id_civico1."' and cc.id='".$id_civico2."';";

} else {
		$query="SELECT st_transform(ST_LineMerge(ST_LineSubstring (
  ST_SnapToGrid(v.geom,1),
  ST_LineLocatePoint(ST_LineMerge(ST_SnapToGrid(v.geom,1)), st_closestpoint(v.geom, cc.geom)),
  ST_LineLocatePoint(ST_LineMerge(ST_SnapToGrid(v.geom,1)), st_closestpoint(v.geom, c.geom))
  )),4326)
as geom
FROM geodb.v_vie_unite v, geodb.civici c, geodb.civici cc
WHERE  v.codvia='".$codvia."' and c.id='".$id_civico1."' and cc.id='".$id_civico2."';";

}


//echo $query;
//exit;
echo "<br>";


	
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$geom1=$r['geom'];
}



if ($geom1=='') {
	$query_g= "SELECT st_centroid(st_transform(geom,4326)) as geom FROM geodb.v_vie_unite WHERE codvia='".$codvia."';";
	$result_g = pg_query($conn, $query_g);
	while($r_g = pg_fetch_assoc($result_g)) {
		$geom1=$r_g['geom'];
	}
	//echo $query_g."<br>";
}



$query_max= "SELECT max(id) FROM segnalazioni.t_provvedimenti_cautelari;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_provvedimento=$r_max["max"]+1;
	} else {
		$id_provvedimento=1;	
	}
}
//echo "Id provvedimento:".$id_provvedimento. "<br>";



if (isset($segn)){
	$query_g= "SELECT id_evento, geom FROM segnalazioni.v_segnalazioni where id='".$segn."';";
	$result_g = pg_query($conn, $query_g);
	while($r_g = pg_fetch_assoc($result_g)) {
		//$geom=$r_g['geom'];
		$id_evento=$r_g['id_evento'];
	}
	//echo $query_g."<br>";
}


// recupero info su U.O. 

$query_uo= "SELECT * FROM users.v_squadre where id='".$uo."';";


$result_uo = pg_query($conn, $query_uo);
while($r_uo = pg_fetch_assoc($result_uo)) {
	$uo_descrizione=$r_uo['nome'];
}
//echo $query_uo."<br>";



/*if ($codvia!=''){
	exit;
}*/


if ($geom1 =='') {
	$query_g= "SELECT st_centroid(st_transform(geom,4326)) as geom FROM ".$nome_tabella_oggetto_rischio. " WHERE ".$nome_campo_id_oggetto_rischio."='".$id_oggetto_rischio."';";
	$result_g = pg_query($conn, $query_g);
	while($r_g = pg_fetch_assoc($result_g)) {
		$geom=$r_g['geom'];
	}
	//echo $query_g."<br>";
}

// qua bisogna aggiungere la verifica su civico o edificio
if($nome_tabella_oggetto_rischio=='geodb.civici'){
	$query_g= "SELECT * FROM ".$nome_tabella_oggetto_rischio. " WHERE ".$nome_campo_id_oggetto_rischio."='".$id_oggetto_rischio."' AND tipooggettoriferimento='IMM_EDIFICIO';";
$result_g = pg_query($conn, $query_g);
while($r_g = pg_fetch_assoc($result_g)) {
	$id_edificio=$r_g['idoggettoriferimento'];
}
//echo $query_g."<br>";
}

//check e segnalazione errore

$check_civico_edificio=0;
$query_ce="SELECT * FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE rimosso='f' AND tipo_oggetto='geodb.edifici' AND id_oggetto=".$id_edificio.";";
$result_ce = pg_query($conn, $query_ce);
while($r_ce = pg_fetch_assoc($result_ce)) {
	$check_civico_edificio=1;
	echo "<h2>ATTENZIONE: L'edificio e' gia' stato oggetto di provvedimento cautelare. <a href=../dettagli_provvedimento_cautelare.php?id=".$r_ce['id']."> visualizza dettagli provvedimento</a>.</h2> <br> ";
	exit;
}
//echo $query_ce."<br>";


$query_ce="SELECT * FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE rimosso='f' AND tipo_oggetto='geodb.civici' AND id_oggetto=".$id_oggetto_rischio.";";
$result_ce = pg_query($conn, $query_ce);
while($r_ce = pg_fetch_assoc($result_ce)) {
	$check_civico_edificio=1;
	echo "<h2>ATTENZIONE: Il civico e' gia'stato oggetto di provvedimento cautelare. <a href=../dettagli_provvedimento_cautelare.php?id=".$r_ce['id']."> visualizza dettagli provvedimento</a></h2> <br> ";
	exit;
}

//echo $query_ce."<br>";



//check sottopassi
$query_ce="SELECT * FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE rimosso='f' AND tipo_oggetto='geodb.sottopassi' AND id_oggetto=".$id_oggetto_rischio.";";
$result_ce = pg_query($conn, $query_ce);
while($r_ce = pg_fetch_assoc($result_ce)) {
	$check_civico_edificio=1;
	echo "<h2>ATTENZIONE: Il sottopasso e' gia'stato oggetto di provvedimento cautelare. <a href=../dettagli_provvedimento_cautelare.php?id=".$r_ce['id']."> visualizza dettagli provvedimento</a></h2> <br> ";
	exit;
}

//echo $query_ce."<br>";

//exit;
//echo "Descrizione uo:".$uo_descrizione. "<br>";







$query= "INSERT INTO segnalazioni.t_provvedimenti_cautelari ( id, descrizione, id_profilo, id_uo, id_tipo, id_evento";

//values
$query=$query.") VALUES (".$id_provvedimento.", '".$descrizione."', '".$profilo_ok."', '". $uo. "', ". $tipo_pc. ", ". $id_evento. "";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);

echo "<br>";


if ($geom1=='') {
	$query= "INSERT INTO segnalazioni.t_geometrie_provvedimenti_cautelari ( id_provvedimento, id_oggetto, tipo_oggetto, geom_inizio";
	$query=$query.") VALUES (".$id_provvedimento.", ".$id_oggetto_rischio.", '". $nome_tabella_oggetto_rischio. "', '". $geom."'";
	
} else {
	if ($id_civico1!='' and $punto1!='' and $punto2 !=''){
		$query= "INSERT INTO segnalazioni.t_geometrie_provvedimenti_cautelari ( id_provvedimento, id_oggetto, tipo_oggetto, geom, id_civico_inizio,id_civico_fine";
		$query=$query.") VALUES (".$id_provvedimento.", ".$id_oggetto_rischio."::integer, '". $nome_tabella_oggetto_rischio. "', '". $geom1."',".$id_civico1.",".$id_civico2."";
	} else {
		$query= "INSERT INTO segnalazioni.t_geometrie_provvedimenti_cautelari ( id_provvedimento, id_oggetto, tipo_oggetto, codvia, geom_inizio, descrizione";
		$query=$query.") VALUES (".$id_provvedimento.", ".$id_oggetto_rischio."::integer, '". $nome_tabella_oggetto_rischio. "','".$codvia."', '". $geom1."','".$desc_via."'";
	}
}
//values

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);

echo "<br>";

//exit;




echo "<br>";


if (isset($id)){
	$query= "INSERT INTO segnalazioni.join_segnalazioni_provvedimenti_cautelari(id_provvedimento, id_segnalazione_in_lavorazione";
	//values
	$query=$query.") VALUES (".$id_provvedimento.", ".$id." ";
	$query=$query.");";
	//echo $query;
	echo "<br>";
	//exit;
	$result=pg_query($conn, $query);
} else {
	// creo una segnalazione fittizia
	/*
	// SEGNALANTE
	$query_max= "SELECT max(id) FROM segnalazioni.t_segnalanti;";
	$result_max = pg_query($conn, $query_max);
	while($r_max = pg_fetch_assoc($result_max)) {
		if ($r_max["max"]>0) {
			$id_segnalante=$r_max["max"]+1;
		} else {
			$id_segnalante=1;	
		}
	}
	echo $id_segnalante;
	echo "<br>";

	$query= "INSERT INTO segnalazioni.t_segnalanti( id, id_tipo_segnalante, nome_cognome";
	//values
	$query=$query.") VALUES (".$id_segnalante.", 4, 'Segnalazione inserita in automatico dal sistema per l'inserimento di un provv. cautelare' ";
	$query=$query.");";
	echo $query;
	$result=pg_query($conn, $query);
	echo "<br>";
	
	
	//SEGNALAZIONE
	$query_max= "SELECT max(id) FROM segnalazioni.t_segnalazioni;";
	$result_max = pg_query($conn, $query_max);
	while($r_max = pg_fetch_assoc($result_max)) {
		if ($r_max["max"]>0) {
			$id_segnalazione=$r_max["max"]+1;
		} else {
			$id_segnalazione=1;	
		}
	}
	echo $id_segnalazione;
	echo "<br>";
	*/
	
}






$query= "INSERT INTO segnalazioni.stato_provvedimenti_cautelari(id_provvedimento, id_stato_provvedimenti_cautelari";

//values
$query=$query.") VALUES (".$id_provvedimento.", 2 ";

$query=$query.");";

//echo $query."<br>";
//exit;
$result=pg_query($conn, $query);


$query="UPDATE users.t_squadre SET id_stato=1 WHERE id=".$uo.";";
//echo $query;
//exit;
$result=pg_query($conn, $query);
echo "<br>";




$query= "INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione( id_segnalazione_in_lavorazione, log_aggiornamento";

//values
$query=$query.") VALUES (".$id.", ' Assegnato nuovo provvedimento cautelare alla seguente squadra: ".$uo_descrizione." - <a class=\"btn btn-info\" href=\"dettagli_provvedimento_cautelare.php?id=".$id_provvedimento."\"> Visualizza dettagli </a>'";

$query=$query.");";

//echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('provvedimenti_cautelari','".$_SESSION["operatore"] ."', 'Inviato provvedimento cautelare ".$id_provvedimento."');";
$result = pg_query($conn, $query_log);




//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
require('../token_telegram.php');

require('../send_message_telegram.php');


$query_telegram="SELECT telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';";
echo $query_telegram;
echo "<br>";

// https://apps.timwhitlock.info/emoji/tables/unicode
// \xE2\x9A\xA0 warning
// \xE2\x80\xBC punti esclamativi

$messaggio="\xE2\x9B\x94 E' stato creato un nuovo provvedimento cautelare, consultare il programma ".$link." ";
if ($notifiche =='f'){
	$messaggio= $messaggio ." (\xE2\x84\xB9 ricevi questo messaggio in quanto operatore di Protezione Civile \xE2\x84\xB9)";
}
$messaggio= $messaggio ."\xE2\x9B\x94";

echo $messaggio;
echo "<br>";
$result_telegram = pg_query($conn, $query_telegram);
while($r_telegram = pg_fetch_assoc($result_telegram)) {
	//echo $r_telegram['telegram_id'];
	//$chat_id = $r_telegram['telegram_id'];
	sendMessage($r_telegram['telegram_id'], $messaggio , $token);
}





//echo $query_log;
$query="SELECT mail FROM users.tipo_mail_provvedimenti_cautelari WHERE valido='t';";
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
$mail->Subject = 'Urgente - Nuovo provvedimento cautelare assegnato tramite il Sistema di Gestione Emergenze del Comune di Genova '.$note_ambiente_mail.'';
//$mail->Subject = 'PHPMailer SMTP without auth test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$body =  'Hai ricevuto questo messaggio in quanto è stato assegnato un nuovo provvedimento cautelare <br>. Ti preghiamo di non rispondere a questa mail automatica, bensì di visualizzare i dettagli del provvedimento cautelare accedendo 
 con le tue credenziali alla pagina <a href="'.$link.'/pages/dettagli_provvedimento_cautelare.php?id='.$id_provvedimento.'" >'.$link.'</a> (il Sistem a di Gestione delle Emergenze del Comune di Genova).
 <br> <br> Protezione Civile del Comune di Genova. <br><br>--<br> Ricevi questa mail  in quanto il tuo indirizzo mail è registrato a sistema. 
 Per modificare queste impostazioni è possibile inviare una mail a salaemergenzepc@comune.genova.it ';

  
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
	echo '<br>Il provvedimento cautelare &egrave stato correttamente assegnato, ma si &egrave riscontrato un problema nell\'invio della mail.';
	echo '<br>Entro 15" verrai re-indirizzato alla pagina della tua segnalazione, clicca al seguente ';
	echo '<a href="../dettagli_provvedimento_cautelare.php?id='.$id_provvedimento.'">link</a> per saltare l\'attesa.</h3>' ;
	//sleep(30);
    header("refresh:15;url=../dettagli_provvedimento_cautelare.php?id=".$id_provvedimento);
} else {
    echo "Message sent!";
	header("location: ../dettagli_provvedimento_cautelare.php?id=".$id_provvedimento);
}



//exit;
//header("location: ../dettagli_segnalazione.php?id=".$segn);


?>
