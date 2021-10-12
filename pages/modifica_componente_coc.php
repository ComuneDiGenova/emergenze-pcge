<?php 
session_start();
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

$id=pg_escape_string($_GET["id"]);

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
    if(isset($_POST['Submit'])){
        $cf=pg_escape_string($_POST["matricolaCf"]);
        $nome=pg_escape_string($_POST["nome"]);
        $cognome=pg_escape_string($_POST["cognome"]);
        $mail=pg_escape_string($_POST["mail"]);
        $telegram=pg_escape_string($_POST["telegramId"]);
        $funzione=pg_escape_string($_POST["addFunzione"]);

        $query = "UPDATE users.utenti_coc SET
            matricola_cf = $1, nome = $2, cognome = $3, mail = $4, telegram_id = $5, funzione = $6
            where id = $7;";
        //$result = pg_query($conn, $query);
        //echo $query_lizmap;
        $result = pg_prepare($conn, "myquery", $query);
        $result = pg_execute($conn, "myquery", array($cf, $nome, $cognome, $mail, $telegram, $funzione, $id));


        header ("Location: lista_utenti_coc.php");
    }
    pg_close($conn);
}
?>