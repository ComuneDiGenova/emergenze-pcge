<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=$_GET["id"];

$query_cf= "SELECT cf FROM users.utenti_esterni;";
$result_cf = pg_query($conn, $query_cf);
while($r_cf = pg_fetch_assoc($result_cf)) {
    if("'".$r_cf['cf']."'"== "'".$_POST['cf']."'") {
        echo "Codice Fiscale già esistente. <br><br>";
        //echo "<a href=\"../update_volontario.php?id=$cf\"> Torna indietro </a><br><br>";
        echo "<a href=\"../lista_volontari.php\"> Torna alla lista volontari e controlla i CF </a>";
        //exit;
    }
}


$query="UPDATE users.utenti_esterni SET id1=".$_POST["id1"].", id2=NULL, id3=NULL WHERE cf=$cf;";
echo $query;
$result = pg_query($conn, $query);
//exit;

$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update anagrafica volontario  CF: ".$_POST['CF']."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../update_volontario.php?id=".$cf);


?>