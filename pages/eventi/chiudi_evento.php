<?php

// Avvio della sessione
session_start();

// Include il file di connessione al database
include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';


// Prende il valore "id" dalla query string e lo protegge da SQL injection
$id = pg_escape_string($_GET["id"]);
$id = str_replace("'", "", $id);

// Aggiorno la tabella "t_eventi"
$query = "UPDATE eventi.t_eventi SET data_ora_fine_evento=now(), valido='FALSE' WHERE id=$id;";
echo $query;
$result = pg_query($conn, $query);

//  Inserisco il log
$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('users', '" . $_SESSION["Utente"] . "', 'Chiusura evento definitiva " . $_POST['id'] . "');";

$result = pg_query($conn, $query_log);

// Chiudi squadre associate all'evento
$query0 = "SELECT c.matricola_cf FROM users.t_componenti_squadre c
JOIN users.t_squadre s ON s.id=c.id_squadra
WHERE s.id_evento=" . $id . " AND c.data_end IS NULL;";
$result0 = pg_query($conn, $query0);
while ($r0 = pg_fetch_assoc($result0)) {
    $query1 = "UPDATE users.t_componenti_squadre 
	SET data_end=now() 
	WHERE matricola_cf = '" . $r0['matricola_cf'] . "';";
    $result1 = pg_query($conn, $query1);
}

// Importo i file necessari per le notifiche Telegram
require('../token_telegram.php');
require('../send_message_telegram.php');

// Query per selezionare la descrizione da "v_eventi" con l'ID dell'evento
$query = "SELECT descrizione FROM eventi.v_eventi WHERE id=" . $id . ";";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
    $descrizione_tipo = $r['descrizione'];
}

// Query per selezionare la nota da "t_note_eventi" con l'ID dell'evento
$query = "SELECT nota FROM eventi.t_note_eventi WHERE id_evento=$id;";
$result = pg_query($conn, $query);
while ($r = pg_fetch_assoc($result)) {
    $nota = $r['nota'];
};

// Query per selezionare gli utenti Telegram
$query_telegram = "SELECT telegram_id from users.utenti_sistema where id_profilo <= 3 and telegram_id !='' and telegram_attivo='t';";

// Formattazione del messaggio
$messaggio = " \xF0\x9F\x94\xB4 L'evento di tipo " . $descrizione_tipo . ", " . $nota . " (id=" . $id . ") è stato chiuso in maniera definitiva.";
$messaggio = $messaggio . " (ricevi questo messaggio in quanto operatore di Protezione Civile) \xF0\x9F\x94\xB4 ";

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
header("location: ../dettagli_evento_c.php");

?>
