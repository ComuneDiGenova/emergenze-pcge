<?php

// Avvio della sessione
session_start();

// Importazione file per la validazione degli input e connessione al db
require('../validate_input.php');
include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';

// Prende il valore "id" dalla query string e lo protegge da SQL injection
$id = pg_escape_string($_GET["id"]);
$id = str_replace("'", "", $id);

// Aggiornamento tabella "t_eventi"
$query = "UPDATE eventi.t_eventi SET valido=NULL, data_ora_chiusura=now() WHERE id=$id;";
echo $query;
$result = pg_query($conn, $query);

// inserimento log
$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('users', '" . $_SESSION["operatore"] . "', 'Chiusura evento " . $_POST['id'] . " - step 0');";
$result = pg_query($conn, $query_log);

// Query per selezionare la nota da "t_note_eventi" con l'ID dell'evento
$query = "SELECT nota FROM eventi.t_note_eventi WHERE id_evento=$id;";

// Esegui la query e recupera la nota
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
    $nota = $r['nota'];
}

// Query per selezionare la descrizione da "v_eventi" con l'ID dell'evento
$query = "SELECT descrizione FROM eventi.v_eventi WHERE id=" . $id . ";";

// Esegui la query e recupera la descrizione
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
    $descrizione_tipo = $r['descrizione'];
}

// Includi i file necessari per le notifiche Telegram
require('../token_telegram.php');
require('../send_message_telegram.php');

// Query per selezionare gli utenti Telegram
$query_telegram = "SELECT telegram_id FROM users.utenti_sistema WHERE id_profilo <= 3 AND telegram_id != '' AND telegram_attivo='t';";

// formattazione messaggio
$messaggio = "\xF0\x9F\x94\x90 L'evento di tipo " . $descrizione_tipo . ", " . $nota . " (id=" . $id . ") è stato messo in chiusura.";
$messaggio = $messaggio . " Non sarà più possibile inserire nuove segnalazioni, ma solo elaborare quelle già inserite a sistema.";
$messaggio = $messaggio . " (ricevi questo messaggio in quanto operatore di Protezione Civile) \xF0\x9F\x94\x90";

echo $messaggio;
echo "<br>";

// Esegui la query dei contatti Telegram
$result_telegram = pg_query($conn, $query_telegram);
while ($r_telegram = pg_fetch_assoc($result_telegram)) {
    sendMessage($r_telegram['telegram_id'], $messaggio, $token);
}

echo "<br>";
echo $query_log;

// Reindirizza alla pagina dei dettagli dell'evento
header("location: ../dettagli_evento_c.php?e=" . $id . "");

?>