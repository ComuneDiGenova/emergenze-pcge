<?php 
session_start();
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

$id=pg_escape_string($_GET["id"]);

if(!$conn) {
    die('Connessione fallita !<br />');
} else {
    if(isset($_POST['Submit'])){
        $inizio_turno=pg_escape_string($_POST["dataInizioTurno"]);
        $durata=pg_escape_string($_POST["durataTurno"]);

        $query = "UPDATE users.t_presenze SET
            data_inizio = $1, durata = $2
            where id = $3;";

        //echo $query_lizmap;
        $result = pg_prepare($conn, "myquery", $query);
        $result = pg_execute($conn, "myquery", array($inizio_turno, $durata, $id));
        header ("Location: elenco_presenti.php");
    }
    pg_close($conn);
}
?>