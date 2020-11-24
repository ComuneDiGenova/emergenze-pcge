<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


$id_squadra=$_GET['s'];

$matricola_cf=$_GET['m'];

$query="UPDATE users.t_componenti_squadre SET capo_squadra='f' 
WHERE matricola_cf= '".$matricola_cf."' AND id_squadra=".$id_squadra.";";
echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION["operatore"] ."', 'Componente ".$matricola_cf." della squadra con id: ".$id_squadra." diventato capo squadra');";
$result = pg_query($conn, $query_log);

//exit;
header("location: ../edit_squadra.php?id=".$id_squadra."");
?>