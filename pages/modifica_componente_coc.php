<?php 
session_start();

include(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

$id=pg_escape_string($_GET["id"]);

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
    if(isset($_POST['Submit'])){
        $cf=pg_escape_literal($_POST["matricolaCf"]);
        $nome=pg_escape_literal($_POST["nome"]);
        $cognome=pg_escape_literal($_POST["cognome"]);
        $mail=pg_escape_literal($_POST["mail"]);
        $telegram=pg_escape_literal($_POST["telegramId"]);
        $funzione=pg_escape_literal($_POST["addFunzione"]);

        $query = "UPDATE users.utenti_coc 
                SET matricola_cf = {$cf}, nome = {$nome}, cognome = {$cognome}, 
                    mail = {$mail}, telegram_id = {$telegram}, funzione = {$funzione}
                WHERE id = {$id};";

        $result = pg_prepare($conn, "myquery", $query);
        $result = pg_execute($conn, "myquery", array());

        // print_r($_POST);
        header ("location: ./lista_utenti_coc.php");
    }
    pg_close($conn);
}
?>