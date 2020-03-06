<?php

function sendMessage($chatID, $messaggio, $token) {
    echo "sending message to " . $chatID . "\n";

    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatID;
    $url = $url . "&text=" . urlencode($messaggio);
    $ch = curl_init();
    $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}


/*$bot_url    = "https://api.telegram.org/bot<bot_id>/";
$url        = $bot_url . "sendPhoto?chat_id=" . $chat_id ;

$post_fields = array('chat_id'   => $chat_id,
    'photo'     => new CURLFile(realpath("/path/to/image.png"))
);

$ch = curl_init(); 
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Content-Type:multipart/form-data"
));
curl_setopt($ch, CURLOPT_URL, $url); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
$output = curl_exec($ch);
*/



function sendPhoto($chatID, $foto, $token) {
    echo "sending foto to " . $chatID . "\n";
    $url = "https://api.telegram.org/bot" . $token . "/sendPhoto?chat_id=" . $chatID;
    $url = $url . "&photo=" . $foto;
    $ch = curl_init();
    $optArray = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

?>

