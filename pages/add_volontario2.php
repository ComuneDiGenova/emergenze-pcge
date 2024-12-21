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

session_start();
include explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';

// Sanifica l'input
$cf = strtoupper($_POST['CF']);
$nome = str_replace("'", "''", $_POST['nome']);
$cognome = str_replace("'", "''", $_POST['cognome']);
$indirizzo = str_replace("'", "''", $_POST['indirizzo']);

// Controlla se il Codice Fiscale esiste già
$query_cf = "SELECT cf FROM users.utenti_esterni WHERE cf ILIKE '$cf';";
$result_cf = pg_query($conn, $query_cf);
while ($r_cf = pg_fetch_assoc($result_cf)) {
    if ($r_cf['cf'] === $cf) {
        echo "Codice Fiscale <b>$cf</b> già esistente. <br><br>";
        echo "<a href='add_volontario.php'>Torna indietro</a>";
        exit;
    }
}

// Costruisce la query INSERT
$query = "INSERT INTO users.utenti_esterni (
    cf, cognome, nome, nazione_nascita, data_nascita, comune_residenza, telefono1, mail";

// Aggiunge i campi opzionali alla query
if (!empty($_POST['indirizzo'])) $query .= ", indirizzo";
if (!empty($_POST['UO_I'])) $query .= ", id1";
if (!empty($_POST['UO_II'])) $query .= ", id2";
if (!empty($_POST['UO_III'])) $query .= ", id3";
if (!empty($_POST['CAP'])) $query .= ", cap";
if (!empty($_POST['telefono2'])) $query .= ", telefono2";
if (!empty($_POST['fax'])) $query .= ", fax";
if (!empty($_POST['num_GG'])) $query .= ", numero_gg";

$query .= ") VALUES (
    '$cf', '$cognome', '$nome', '" . $_POST['naz'] . "',
    '" . $_POST['yyyy'] . "-" . $_POST['mm'] . "-" . $_POST['dd'] . "',
    '" . $_POST['comune'] . "', '" . $_POST['telefono1'] . "', '" . $_POST['mail'] . "'";

// Aggiunge i valori opzionali alla query
if (!empty($indirizzo)) $query .= ", '$indirizzo'";
if (!empty($_POST['UO_I'])) $query .= ", " . $_POST['UO_I'];
if (!empty($_POST['UO_II'])) $query .= ", " . substr($_POST['UO_II'], -1);
if (!empty($_POST['UO_III'])) $query .= ", " . substr($_POST['UO_III'], -1);
if (!empty($_POST['CAP'])) $query .= ", '" . $_POST['CAP'] . "'";
if (!empty($_POST['telefono2'])) $query .= ", '" . $_POST['telefono2'] . "'";
if (!empty($_POST['fax'])) $query .= ", '" . $_POST['fax'] . "'";
if (!empty($_POST['num_GG'])) $query .= ", '" . $_POST['num_GG'] . "'";

$query .= ");";

// Esegue la query
$result = pg_query($conn, $query);

// Registra l'operazione nel log
$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES (
    'users', '" . $_SESSION['Utente'] . "', 
    'Insert volontario " . $_POST['cognome'] . " " . $_POST['nome'] . " - CF: " . $_POST['CF'] . "');";
$result_log = pg_query($conn, $query_log);

// Reindirizza alla pagina della lista volontari
header("Location: lista_volontari.php");
exit;
?>
</body>
</html>
