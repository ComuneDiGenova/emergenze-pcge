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
        #$fine= "'".$durata." hours'";
        $fine= $durata." hours";
        #$fine_hp = str_replace("\"","'",$fine);
        //echo $fine;
        #echo $fine_hp;

        $query="UPDATE users.t_presenze SET data_inizio = '".$inizio_turno."', durata = '".$durata."' where id = ".$id.";";
        $result=pg_query($conn, $query);
        //echo $query;
        $query1="UPDATE users.t_presenze SET data_fine_hp = data_inizio + interval '".$fine."'
        where id = ".$id.";";
        $result=pg_query($conn, $query1);
        //echo $query;
        //echo $query1;
        
        //capire perchè con questo metodo di scrittura della query non si riesce a passare il valore di interval
        /* $query = "UPDATE users.t_presenze SET
            data_inizio = $1, durata = $2, data_fine_hp = data_inizio + interval $3
            where id = $4;";
        //$result = pg_query($conn, $query);
        //echo $query_lizmap;
        $result = pg_prepare($conn, "myquery", $query);
        $result = pg_execute($conn, "myquery", array($inizio_turno, $durata, $fine, $id)); */
        //header ("Location: elenco_presenti.php");
    }
    pg_close($conn);
}
?>