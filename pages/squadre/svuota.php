<?php

session_start();
require('../validate_input.php');

//echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id_squadra=$_GET['id'];

$query="UPDATE users.t_componenti_squadre SET data_end=now() 
WHERE id_squadra=".$id_squadra." AND data_end IS NULL;";
echo $query;
//exit;
$result=pg_query($conn, $query);

// $query="DELETE FROM users.t_mail_squadre 
// WHERE cod='".$id_squadra."';";
// echo $query;
// //exit;
// //lasciamo perdere
// $result=pg_query($conn, $query);

// $query="DELETE FROM users.t_telefono_squadre 
// WHERE cod='".$id_squadra."';";
// echo $query;
// //exit;
// //lasciamo perdere
// $result=pg_query($conn, $query);

$query = "SELECT count(c.id_squadra) AS count FROM users.v_componenti_squadre c
 WHERE c.id_squadra = ".$id_squadra." and c.data_end is null ";
$result=pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	if ($r["count"]==0){
		$query2="UPDATE users.t_squadre SET id_stato=2 WHERE id=".$id_squadra.";";
		$result2=pg_query($conn, $query2);
	}
}

$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$operatore ."', 'Squadra con id: ".$id." svuotata');";
$result = pg_query($conn, $query_log);

//exit;
header("location: ../gestione_squadre.php");
?>