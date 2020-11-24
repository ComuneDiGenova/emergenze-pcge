<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET['id'];

$query="UPDATE users.t_squadre SET da_nascondere='t' WHERE id=".$id.";";
echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', 'Squadra con id: ".$id." nascosta');";
$result = pg_query($conn, $query_log);

//exit;
header("location: ../gestione_squadre.php");
?>