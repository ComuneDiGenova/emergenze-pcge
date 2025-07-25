<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="content-type" content="text/html; charset=UTF8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error</title>
</head>
<body>
<?php

// Inizio della sessione
session_start();

// Includi connessione con verifica
try {
    $conn_path = explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
    if (!file_exists($conn_path)) {
        throw new Exception("File di connessione non trovato.");
    }
    include $conn_path;
} catch (Exception $e) {
    echo "Errore: " . $e->getMessage();
    exit;
}

// Sanifica e valida l'input
$cf = strtoupper(trim($_POST['CF'] ?? ''));
if (!preg_match('/^[A-Z0-9]{16}$/', $cf)) {
    echo "Codice Fiscale non valido. Torna indietro e riprova.";
    exit;
}

$nome = strtoupper(pg_escape_string(trim($_POST['nome'] ?? '')));
$cognome = strtoupper(pg_escape_string(trim($_POST['cognome'] ?? '')));
$indirizzo = pg_escape_string(trim($_POST['indirizzo'] ?? ''));

// Controlla se il Codice Fiscale esiste già
$query_cf = "SELECT cf FROM users.utenti_esterni WHERE cf ILIKE '$cf';";
$result_cf = pg_query($conn, $query_cf);
if (pg_num_rows($result_cf) > 0) {
    echo "Codice Fiscale <b>$cf</b> già esistente. <br><br>";
    echo "<a href='add_volontario.php'>Torna indietro</a>";
    exit;
}

// Costruisce la query INSERT
$query_fields = ["cf", "cognome", "nome", "nazione_nascita", "data_nascita", "comune_residenza", "telefono1", "mail"];
$query_values = ["'$cf'", "'$cognome'", "'$nome'", "'" . pg_escape_string($_POST['naz'] ?? '') . "'", 
    "'" . pg_escape_string($_POST['yyyy'] . '-' . $_POST['mm'] . '-' . $_POST['dd']) . "'", 
    "'" . pg_escape_string($_POST['comune'] ?? '') . "'", "'" . pg_escape_string($_POST['telefono1'] ?? '') . "'", 
    "'" . pg_escape_string($_POST['mail'] ?? '') . "'"];

// Aggiunge i campi opzionali
if (!empty($indirizzo)) {
    $query_fields[] = "indirizzo";
    $query_values[] = "'$indirizzo'";
}
if (!empty($_POST['UO_I'])) {
    $query_fields[] = "id1";
    $query_values[] = (int) $_POST['UO_I'];
}
if (!empty($_POST['UO_II'])) {
    $query_fields[] = "id2";
    $query_values[] = (int) $_POST['UO_II'];
}
if (!empty($_POST['cap'])) {
    $query_fields[] = "cap";
    $query_values[] = "'" . pg_escape_string($_POST['cap']) . "'";
}
if (!empty($_POST['telefono2'])) {
    $query_fields[] = "telefono2";
    $query_values[] = "'" . pg_escape_string($_POST['telefono2']) . "'";
}
if (!empty($_POST['fax'])) {
    $query_fields[] = "fax";
    $query_values[] = "'" . pg_escape_string($_POST['fax']) . "'";
}
if (!empty($_POST['num_GG'])) {
    $query_fields[] = "numero_gg";
    $query_values[] = "'" . pg_escape_string($_POST['num_GG']) . "'";
}

$query = "INSERT INTO users.utenti_esterni (" . implode(", ", $query_fields) . ") VALUES (" . implode(", ", $query_values) . ");";

// Esegue la query
$result = pg_query($conn, $query);
if (!$result) {
    echo "Errore durante l'inserimento. Torna indietro e riprova.";
    exit;
}

// Registra l'operazione nel log
$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES (
    'users', '" . pg_escape_string($_SESSION['Utente'] ?? 'anonimo') . "', 
    'Insert volontario $cognome $nome - CF: $cf');";
$result_log = pg_query($conn, $query_log);
if (!$result_log) {
    echo "Errore durante la registrazione del log.";
    exit;
}

// Reindirizza alla pagina della lista volontari
header("Location: lista_volontari.php");
exit;
?>
</body>
</html>
