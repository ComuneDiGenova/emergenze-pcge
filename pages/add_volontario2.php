<!DOCTYPE html>
<html>
<head>


<meta http-equiv="content-type" content="text/html; charset=UTF8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title> Error </title>

</head>
<body>
<?php

session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
$cf=strtoupper($_POST['CF']);

$nome= str_replace("'", "''", $_POST["nome"]);

$cognome= str_replace("'", "''", $_POST["cognome"]);

$indirizzo= str_replace("'", "''", $_POST["indirizzo"]);




// controllo CF
$query_cf= "SELECT cf FROM users.utenti_esterni where cf ilike '".$cf."';";
$result_cf = pg_query($conn, $query_cf);
while($r_cf = pg_fetch_assoc($result_cf)) {
    if("'".$r_cf['cf']."'"== "'".$cf."'") {
        echo "Codice Fiscale <b>".$cf."</b> già esistente. <br><br>";
        echo "<a href=\"add_volontario.php\"> Torna indietro </a>";
        exit;
    }
}

//echo $query_via;

#echo $_POST['nome']; 
echo "<br>";


$query = "INSERT INTO users. utenti_esterni(cf,
        cognome,
        nome,
        nazione_nascita,
        data_nascita,
        comune_residenza,
        telefono1,
        mail";

if ($_POST['indirizzo']!=null){
    $query=$query.",indirizzo";
}
if ($_POST['UO_I']!=null){
    $query=$query.",id1";
}
if ($_POST['UO_II']!=null){
    $query=$query.",id2";
}
if ($_POST['UO_III']!=null){
    $query=$query.",id3";
}
if ($_POST['CAP']!=null){
    $query=$query.",cap";
}

if ($_POST['telefono2']!=null){
    $query=$query.",telefono2";
}

if ($_POST['fax']!=null){
    $query=$query.",fax";
}
if ($_POST['num_GG']!=null){
    $query=$query.",numero_gg";
}

$query=$query.") VALUES ('".$cf."' ,'".$cognome."' ,'".$nome."' ,'".$_POST['naz']."' ,'".$_POST['yyyy']."-".$_POST['mm']."-".$_POST['dd']."' ,'".$_POST['comune']."', '".$_POST['telefono1']."','".$_POST['mail']."'";

if ($indirizzo!=null){
    $query=$query.",'".$indirizzo."'";
}
if ($_POST['UO_I']!=null){
    $query=$query.",".$_POST['UO_I']."";
}
if ($_POST['UO_II']!=null){
    $query=$query.",".substr($_POST['UO_II'],-1)."";
}
if ($_POST['UO_III']!=null){
    $query=$query.",".substr($_POST['UO_III'],-1)."";
}

if ($_POST['CAP']!=null){
    $query=$query.",'".$_POST['cap']."'";
}

if ($_POST['telefono2']!=null){
    $query=$query.",'".$_POST['telefono2']."'";
}

if ($_POST['fax']!=null){
    $query=$query.",'".$_POST['fax']."'";
}
if ($_POST['num_GG']!=null){
    $query=$query.",'".$_POST['num_GG']."'";
}
$query=$query.");";


echo $query;
//exit;

$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["Utente"] ."', 'Insert volontario ".$_POST['cognome']." ".$_POST['nome']." - CF: ".$_POST['CF']."');";
$result = pg_query($conn, $query_log);

//$idfascicolo=str_replace('A','',$idfascicolo);
//$idfascicolo=str_replace('B','',$idfascicolo);
echo "<br>";
echo $query_log;
//exit;
header("location: lista_volontari.php");
?>

</body>
</html>
