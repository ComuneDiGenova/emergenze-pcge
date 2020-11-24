<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$table= $_GET["t"];
$schema= $_GET["s"];





//echo $schema;
//echo "<br>";
//echo $table;

$i=1;
echo "<br>";

$query = "INSERT INTO ".$schema.".".$table." ";
$query = $query." ( ";

foreach ($_POST as $param_name => $param_val) {
	if ($param_name !='id') {
		if ($i > 1){
			$query = $query.",";
		}
   	$query = $query." " .$param_name. " ";
   	$i=$i+1;
   }
}

$query = $query." ) VALUES ( ";

$i=1;
foreach ($_POST as $param_name => $param_val) {
	if ($param_name !='id') {
		if ($i > 1){
			$query = $query.",";
		}
   	$query = $query." '" .$param_val. "' ";
   	$i=$i+1;
   }
}

$query = $query." );";


//$query = $query." WHERE id=".$_POST["id"].";";

//echo $query;


$result = pg_query($conn, $query);


//exit;

// redirect verso pagina interna
header("location: ../elenco_amm.php?s=".$schema."&t=".$table."");


?>