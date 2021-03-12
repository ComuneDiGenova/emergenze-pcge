<?php

require('./token_telegram.php');

require('./send_message_telegram.php');


$messaggio=" \xF0\x9F\x94\xB4 Messaggio automatico inviato tramite BOT della PC.";
$messaggio= $messaggio ." (ora festeggiare pure, caro Roberto)";
$messaggio= $messaggio ." \xF0\x9F\x94\xB4 ";


//$file= explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/img/omirl.png';
$file = '../img/omirl.png';
echo $file;
echo "<br>";
echo $messaggio;
echo "<br>";
$result_telegram = pg_query($conn, $query_telegram);
sendPhoto($channel, $messaggio , $file, $token);


?>