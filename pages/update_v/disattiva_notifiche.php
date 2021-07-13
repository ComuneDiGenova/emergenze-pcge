<?php 

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

$cf=pg_escape_string($_GET['cf']);

//$query = "UPDATE users.utenti_sistema SET telegram_attivo='f' where matricola_cf='".$cf."';";
//$result = pg_query($conn, $query);
$query = "UPDATE users.utenti_esterni SET telegram_attivo='f' where cf=$1;";
$result = pg_prepare($conn, "myquery", $query);
$result = pg_execute($conn, "myquery", array($cf));


echo pg_last_error($conn);
//exit;

header('Location: ' . $_SERVER['HTTP_REFERER']);

?>