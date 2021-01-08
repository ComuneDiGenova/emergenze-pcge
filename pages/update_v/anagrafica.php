<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$cf=pg_escape_string($_GET["id"]);

$nome=pg_escape_string($_POST["nome"]);
$cognome=pg_escape_string($_POST["cognome"]);
$nazione_nascita=pg_escape_string($_POST["nazione_nascita"]);
$data_nascita=pg_escape_string($_POST["data_nascita"]);





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


//$query="UPDATE users.utenti_esterni SET cf='".$_POST["cf"]."', nome='".$_POST["nome"]."', cognome='".$_POST["cognome"]."', nazione_nascita='".$_POST["nazione_nascita"]."', data_nascita='".$_POST["data_nascita"]."' where cf=$cf;";
//echo $query;
//$result = pg_query($conn, $query);
$query="UPDATE users.utenti_esterni SET cf=$1, nome=$2, cognome=$3, nazione_nascita=$4, data_nascita=$5 where cf=$1";
$result = pg_prepare($conn, "myquery", $query);
$result = pg_execute($conn, "myquery", array($cf,$nome,$cognome,$nazione_nascita,$data_nascita));


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Update anagrafica volontario  CF: ".$_POST['CF']."');";
$result = pg_query($conn, $query_log);



//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;

//exit;
header("location: ../update_volontario.php?id=".$cf);


?>