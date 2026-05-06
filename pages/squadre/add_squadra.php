<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
//require('../check_evento.php');


$id_squadra=$_GET['s'];

$matricola_cf=$_POST['cf'];
// Normalizza input (evita doppie submit e SQL injection basilare)
$id_squadra = (int)$id_squadra;
$matricola_cf = pg_escape_string($conn, $matricola_cf);

// Inserimento idempotente: evita duplicati "attivi" (data_end IS NULL)
$query="INSERT INTO users.t_componenti_squadre(id_squadra, matricola_cf)
SELECT ".$id_squadra.", '".$matricola_cf."'
WHERE NOT EXISTS (
	SELECT 1
	FROM users.t_componenti_squadre
	WHERE id_squadra = ".$id_squadra."
	  AND matricola_cf = '".$matricola_cf."'
	  AND data_end IS NULL
);";
$result=pg_query($conn, $query);


$mail = '';
$telefono = '';
$query_mail="SELECT mail, telefono1 FROM users.v_utenti_esterni WHERE cf='".$matricola_cf."';";
$result_mail=pg_query($conn, $query_mail);
while($r_mail= pg_fetch_assoc($result_mail)) { 
	$mail=$r_mail['mail'];
	$telefono=$r_mail['telefono1'];
}


if ($mail!=''){
	$mail_esc = pg_escape_string($conn, $mail);
	$query="INSERT INTO users.t_mail_squadre(cod, matricola_cf, mail)
	SELECT ".$id_squadra.", '".$matricola_cf."', '".$mail_esc."'
	WHERE NOT EXISTS (
		SELECT 1 FROM users.t_mail_squadre
		WHERE cod = ".$id_squadra."
		  AND mail = '".$mail_esc."'
	);";
	$result=pg_query($conn, $query);
}



if ($telefono!=''){
	$telefono_esc = pg_escape_string($conn, $telefono);
	$query="INSERT INTO users.t_telefono_squadre(cod, matricola_cf, telefono)
	SELECT ".$id_squadra.", '".$matricola_cf."', '".$telefono_esc."'
	WHERE NOT EXISTS (
		SELECT 1 FROM users.t_telefono_squadre
		WHERE cod = ".$id_squadra."
		  AND matricola_cf = '".$matricola_cf."'
		  AND telefono = '".$telefono_esc."'
	);";
	$result=pg_query($conn, $query);
}


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["operatore"] ."', 'Aggiunto componente a squadra con id: ".$id_squadra."');";
$result = pg_query($conn, $query_log);

//exit;
header("location: ../edit_squadra.php?id=".$id_squadra."");
?>