<?php

include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';

$getfiltri=$_GET["f"];
$filtro_evento_attivo=$_GET["a"];
$filtro_municipio=$_GET["m"];


$pagina=$_POST["pagina"];

$query='SELECT * FROM segnalazioni.tipo_criticita where valido=\'t\' ;';
$result = pg_query($conn, $query);
#echo $result;
//exit;
//$rows = array();
$filter='';
while($r = pg_fetch_assoc($result)) {
    $name='filter'.$r['id'];
    if ($_POST["$name"]==1) {
        $filter=$filter.'1';
    } else {
         $filter=$filter.'0';
    }
}


header("Location: ../".$pagina."?f=".$filter."&a=".$filtro_evento_attivo."&m=".$filtro_municipio."");

echo $filter; 



?>