<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
//require('../check_evento.php');


$id_squadra=$_GET['s'];

$matricola_cf=$_POST['cf'];

echo "Id squadra=".$id_squadra."<br>";
echo "matricola_cf=".$matricola_cf."<br>";
//exit;

$query="INSERT INTO users.t_componenti_squadre(id_squadra, matricola_cf) 
VALUES (".$id_squadra.", '".$matricola_cf."');";
echo $query;
//exit;
$result=pg_query($conn, $query);
echo "<br>";


$query_mail="SELECT mail, telefono1 FROM users.v_utenti_esterni WHERE cf='".$matricola_cf."';";
$result_mail=pg_query($conn, $query_mail);
while($r_mail= pg_fetch_assoc($result_mail)) { 
	$mail=$r_mail['mail'];
	$telefono=$r_mail['telefono1'];
}

echo $query_mail."<br>";


if ($mail!=''){
	$query="INSERT INTO users.t_mail_squadre(cod, matricola_cf, mail) 
	VALUES (".$id_squadra.", '".$matricola_cf."','".$mail."');";
	echo $query;
	//exit;
	$result=pg_query($conn, $query);
}



if ($telefono!=''){
	$query="INSERT INTO users.t_telefono_squadre(cod, matricola_cf, telefono) 
	VALUES (".$id_squadra.", '".$matricola_cf."','".$telefono."');";
	echo $query;
	//exit;
	$result=pg_query($conn, $query);
}


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["operatore"] ."', 'Aggiunto componente a squadra con id: ".$id_squadra."');";
$result = pg_query($conn, $query_log);

//exit;
header("location: ../edit_squadra.php?id=".$id_squadra."");
?>