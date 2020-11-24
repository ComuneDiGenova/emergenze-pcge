<?php

session_start();
require('../validate_input.php');

echo $_SESSION['user'];

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';



$nome= str_replace("'", "''", $_POST["nome"]);
$afferenza=$_POST["afferenza"];
$evento=$_POST["evento"];

echo "permanente=".$_POST["permanente"]."<br>";

if($_POST["permanente"]=='on') {
	$evento_ok= NULL;
} else {
	$evento_ok=$evento;
} 

$query_max= "SELECT max(id) FROM users.t_squadre;";
$result_max = pg_query($conn, $query_max);
while($r_max = pg_fetch_assoc($result_max)) {
	if ($r_max["max"]>0) {
		$id_squadra=$r_max["max"]+1;
	} else {
		$id_squadra=1;	
	}
}

echo $id_squadra;
echo "<br>";


echo "Nome squadra:".$nome. "<br>";
echo "Afferenza:".$afferenza. "<br>";
echo "Evento:".$evento_ok. "<br>";

//exit;



//echo "<h2>La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2> <br> ";



if($_POST["permanente"]=='on') {
	$query= "INSERT INTO users.t_squadre( id, nome, id_stato, cod_afferenza";
	//values
	$query=$query.") VALUES (".$id_squadra.", '".$nome."', 2 , '".$afferenza."' ";
} else {
	$query= "INSERT INTO users.t_squadre( id, nome, id_evento, id_stato, cod_afferenza";
	//values
	$query=$query.") VALUES (".$id_squadra.", '".$nome."', ".$evento_ok.", 2 , '".$afferenza."' ";
}
$query=$query.");";


echo $query;
//exit;
$result=pg_query($conn, $query);



echo "<br>";



// check if checkbox for permanent team is selected
/*if($_POST["permanente"]=='on') {
	
	$query= "INSERT INTO users.t_squadre_permanenti( nome, cod_afferenza";
$query=$query.") VALUES ('".$nome."', '".$afferenza."' ";
$query=$query.");";
echo $query;
//exit;
$result=pg_query($conn, $query);
	
}*/


$query= "INSERT INTO users.t_storico_squadre(id_squadra, log_aggiornamento";

//values
$query=$query.") VALUES (".$id_squadra.", 'Creata nuova squadra: ".$nome."'";

$query=$query.");";

echo $query;
//exit;
$result=pg_query($conn, $query);


$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('users','".$_SESSION['user'] ."', 'Creata squadra con id: ".$id_squadra."');";
$result = pg_query($conn, $query_log);






//exit;
header("location: ../gestione_squadre.php");


?>
