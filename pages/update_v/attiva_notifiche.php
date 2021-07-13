<?php 

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

$cf=$_GET['cf'];

$query = "UPDATE users.utenti_esterni SET telegram_attivo='t' where cf='".$cf."';";
$result = pg_query($conn, $query);

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>