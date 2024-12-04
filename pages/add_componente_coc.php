<?php

    session_start();

    include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

    if(!$conn) {
        die('Connessione fallita !');
    } else {
        if(isset($_POST['Add'])){
            $cf=pg_escape_string($_POST["addMatricolaCf"]);
            $nome=pg_escape_string($_POST["addNome"]);
            $cognome=pg_escape_string($_POST["addCognome"]);
            $mail=pg_escape_string($_POST["addMail"]);
            $telegram=pg_escape_string($_POST["addTelegram"]);
            $funzione=pg_escape_string($_POST["addFunzione"]);

            // query inserimento utente
            $query="INSERT INTO users.utenti_coc 
                        (matricola_cf, nome, cognome, mail, telegram_id, funzione) 
                    VALUES ($1, $2, $3, $4, $5, $6);";
            $result = pg_prepare($conn,"insert_user", $query);
            $result = pg_execute($conn,"insert_user", array($cf, $nome, $cognome, $mail, $telegram, $funzione));

            // query scrittura log
            $query_log="INSERT INTO varie.t_log 
                            (schema, operatore, operazione) 
                        VALUES ('users', $1, 'Aggiunto componente COC CF: $2');";
            $result = pg_prepare($conn,"insert_log", $query_log);
            $result = pg_execute($conn,"insert_log", array($_SESSION["Utente"], $cf));


            header("location: ./lista_utenti_coc.php");
            exit;
        }
        pg_close($conn);
    }

?>