<?php

session_start();

//echo $_SESSION['user'];

include '/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php';

$id=$_GET['id'];

$query="UPDATE users.t_squadre SET da_nascondere='f' WHERE id=".$id.";";
echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', 'Squadra con id: ".$id." visualizzata');";
$result = pg_query($conn, $query_log);

//exit;
header("location: ../gestione_squadre.php");
?>