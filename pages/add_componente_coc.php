<?php

session_start();
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
    if(isset($_POST['Add'])){
        $cf=pg_escape_string($_POST["addMatricolaCf"]);
        $nome=pg_escape_string($_POST["addNome"]);
        $cognome=pg_escape_string($_POST["addCognome"]);
        $mail=pg_escape_string($_POST["addMail"]);
        $telegram=pg_escape_string($_POST["addTelegram"]);

        $query="INSERT INTO users.utenti_coc(matricola_cf, nome, cognome, mail, telegram_id) VALUES ($1, $2, $3, $4, $5);";
        //echo $query;
        //exit;
        $result = pg_prepare($conn,"myquery", $query);
        $result = pg_execute($conn,"myquery", array($cf, $nome, $cognome, $mail, $telegram));

        $query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users',$1, 'Aggiunto componente COC CF: $2');";
        $result = pg_prepare($conn,"myquery2", $query_log);
        $result = pg_execute($conn,"myquery2", array($_SESSION["Utente"], $cf));


        //$idfascicolo=str_replace('A','',$idfascicolo);
        //$idfascicolo=str_replace('B','',$idfascicolo);
        echo "<br>";
        echo $query_log;

        //exit;
        header("location: ./lista_utenti_coc.php");
    }
    pg_close($conn);
}

?>