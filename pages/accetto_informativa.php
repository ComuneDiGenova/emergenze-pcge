<?php

session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$cf=$_GET['id'];

$query="UPDATE users.utenti_sistema SET privacy='t' WHERE matricola_cf='".$cf."';";

$result = pg_query($conn, $query);

echo $query;

//exit;

header('Location: ' . $_SERVER['HTTP_REFERER']);


?>