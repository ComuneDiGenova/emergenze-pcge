<?php

session_start();

//echo $_SESSION['user'];
$allegato_array='';

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');

echo "<h2> La gestione degli incarichi e' attualmente in fase di test and debug. Ci scusiamo per il disagio</h2>";


//$id=$_GET["id"];
$id=str_replace("'", "", $_GET['id']); //sopralluogo


$id_lavorazione=$_POST["id_lavorazione"];



$note= str_replace("'", "''", $_POST["note"]);
$uo=$_POST["uo"];
$id_evento=$_POST["id_evento"];


echo "sopralluogo:".$id. "<br>";
echo "Note:".$note. "<br>";

//exit;



// Count total files
$countfiles = count(array_filter($_FILES['userfile_c']['name']));
//echo $countfiles;
//exit;
 
 // Looping all files
 for($i=0;$i<$countfiles;$i++){
   $filename = $_FILES['userfile_c']['name'][$i];
   
   // Upload file (example from internet)
   //move_uploaded_file($_FILES['file']['tmp_name'][$i],'upload/'.$filename);


// per prima cosa verifico che il file sia stato effettivamente caricato
/*if (!isset($_FILES['userfile_c']) || !is_uploaded_file($_FILES['userfile_c']['tmp_name'])) {
  echo 'Non hai inviato nessun file...';    
} else {*/

	//percorso della cartella dove mettere i file caricati dagli utenti


	$uploaddir0="../../../emergenze_uploads/";

	$uploaddir1= $uploaddir0. "e_".$id_evento."/";

	if (file_exists($uploaddir1)) {
		echo "The file $uploaddir1 exists <br>";
	} else {
		echo "The file $uploaddir1 does not exist <br>";
		$crea_folder="mkdir ".$uploaddir1;
		exec($crea_folder);
	}

	$uploaddir= $uploaddir1. "pc_".$id."/";

	if (file_exists($uploaddir)) {
		echo "The file $uploaddir exists <br>";
	} else {
		echo "The file $uploaddir does not exist <br>";
		$crea_folder="mkdir ".$uploaddir;
		exec($crea_folder);
	}

	//Recupero il percorso temporaneo del file
	$userfile_tmp = $_FILES['userfile_c']['tmp_name'][$i];

	//recupero il nome originale del file caricato e tolgo gli spazi
	//$userfile_name = $_FILES['userfile_c']['name'];
	$userfile_name = preg_replace("/[^a-z0-9\_\-\.]/i", '', basename($_FILES['userfile_c']["name"][$i]));


	$datafile=date("YmdHis");
	$allegato=$uploaddir .$datafile."_". $userfile_name;

	//echo $allegato."<br>";

	//copio il file dalla sua posizione temporanea alla mia cartella upload
	if (move_uploaded_file($userfile_tmp, $allegato)) {
	  //Se l'operazione è andata a buon fine...
	  echo 'File inviato con successo.';
	}else{
	  //Se l'operazione è fallta...
	  echo 'Upload NON valido!'; 
	}


	$allegato=str_replace("../../../", "", $allegato); //allegato database
	if ($allegato_array==''){
		$allegato_array=$allegato;
	} else {
		$allegato_array=$allegato_array .";". $allegato;
	}
}



//exit;





$query= "INSERT INTO segnalazioni.t_comunicazioni_provvedimenti_cautelari(id_provvedimento, testo";
if (isset($allegato)){
	$query= $query . ", allegato";
}
$query= $query .")VALUES (".$id.", '".$note."'";
if (isset($allegato)){
	$query= $query . ",'". $allegato_array."'";
}
$query= $query .");";


echo $query."<br>";
//exit;
$result=pg_query($conn, $query);
echo "Result:". $result."<br>";



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('Provvedimenti cautelari','".$operatore ."', 'Inviata comunicazione a PC (provvedimento cautelare ".$id.")');";
echo $query_log."<br>";
$result = pg_query($conn, $query_log);


echo "Result:". $result."<br>";



//exit;
header("location: ../dettagli_provvedimento_cautelare.php?id=".$id);


?>