<?php

session_start();

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');



$table= $_GET["t"];
$schema= $_GET["s"];

$id= $_GET["id"];

$query="select * from information_schema.columns WHERE table_schema='".$schema."' and table_name ilike'".$table."' and 
ordinal_position= ( SELECT min(ordinal_position) 
from information_schema.columns WHERE table_schema='".$schema."' and table_name ilike'".$table."'
);";
echo $query;
$result = pg_query($conn, $query);
#exit;
while($r = pg_fetch_assoc($result)) {
	$column_id=$r['column_name'];
}


//exit;


//echo $schema;
//echo "<br>";
//echo $table;

$i=1;
echo "<br>";

$query = "UPDATE ".$schema.".".$table." SET";

foreach ($_POST as $param_name => $param_val) {
	echo is_numeric($param_val)."<br>";	
	if ($param_name !='id') {
		if ($param_val != '' and is_numeric($param_val)==0 ){
   		if ($i > 1){
				$query = $query.",";
			}
   		$query = $query." " .$param_name. " = '" .$param_val. "'";
   	} else if(is_numeric($param_val)==1) {
   		if ($i > 1){
				$query = $query.",";
			}
   		$query = $query." " .$param_name. " = " .$param_val. " ";
   	}
   	$i=$i+1;
   }
}
$query = $query." WHERE ".$column_id."=".$id.";";

echo $query;

//exit;
$result = pg_query($conn, $query);


//exit;

// redirect verso pagina interna
header("location: ../elenco_amm.php?s=".$schema."&t=".$table."");


?>