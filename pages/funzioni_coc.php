<?php
session_start();
//require('../validate_input.php');;


require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');


$query1="SELECT * From \"users\".\"join_tipo_funzione_coc\" ORDER BY id;";
//echo $query1;
$result1 = pg_query($conn, $query1);
	while($r1 = pg_fetch_assoc($result1)) {
		$tipo_funzione[]=array($r1["id"],$r1["funzione"]);
		//echo $r1["id"];
		//echo $r1["funzione"];
	}
?>       