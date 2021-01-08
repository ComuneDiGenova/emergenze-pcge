<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=pg_escape_string($_GET["id"]);

$numero_gg=pg_escape_string($_POST["numero_gg"]);
echo "<br>".$numero_gg;
$query="UPDATE users.utenti_esterni SET numero_gg=$1 WHERE cf=$2;";
$result = pg_prepare($conn, "myquery1", $query);
$result = pg_execute($conn, "myquery1", array($numero_gg,$cf));


/*
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
*/

/*
$query="UPDATE users.utenti_esterni SET numero_gg='".$_POST["numero_gg"]."' WHERE cf=$cf;";
echo $query;
$result = pg_query($conn, $query);*/


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update anagrafica volontario  CF: ".$_POST['CF']."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../update_volontario.php?id=".$cf);


?>