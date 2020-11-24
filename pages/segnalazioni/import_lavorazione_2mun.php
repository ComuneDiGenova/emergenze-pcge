<?php
// Start the session
session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id=$_GET["id"];
$id_lavorazione=$_GET["idl"];
//echo $id.'<br>';

$query="UPDATE segnalazioni.join_segnalazioni_in_lavorazione SET sospeso='f' 
WHERE id_segnalazione_in_lavorazione=".$id_lavorazione.";";
//echo $query;
$result = pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$_SESSION["operatore"] ."', 'Presa in carico segnalazione anche da parte di PC ".$id_segnalazione."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;


//exit;
header("location: ../dettagli_segnalazione.php?id=".$id);

?>