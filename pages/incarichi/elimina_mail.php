<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$uo=$_GET["cod"];
$mail=$_GET["mail"];
$idt=$_GET["idt"];


echo "<br>";
if($idt!=''){
    $query="DELETE FROM users.t_mail_incarichi WHERE cod='".$uo."' AND mail='".$mail."' AND id_telegram=".$idt."';";
    $testo = "Eliminati mail ".$mail." e telegra id ".$idt." dal Unit� Operativa ".$uo;
    echo $testo;
}else{
    $query="DELETE FROM users.t_mail_incarichi WHERE cod='".$uo."' AND mail='".$mail."';";
    $testo = "Eliminata mail ".$mail." da Unit� Operativa ".$uo;
    echo $testo;
}

echo $query;
//exit;
$result = pg_query($conn, $query);
echo "<br>";


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', '".$testo."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../edit_mail_uo.php?id=".$uo);


?>