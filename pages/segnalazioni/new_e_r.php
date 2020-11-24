<?php

session_start();
require('../validate_input.php');

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';
require('../check_evento.php');


$id_segnalazione_lav=$_POST["id_lavorazione"];

$id=$_POST["id"];
echo $profilo;


echo "Id".$id."<br>";

echo "Id_lav".$id_segnalazione_lav."<br>";


//exit;



$geom="ST_GeomFromText('POINT(".$_POST["lon"]." ".$_POST["lat"].")',4326)";



	if($_POST["tipo_oggetto"]!='') {
		// devo cercare l'oggetto più vicino
		// 1 . id tipo oggetto da form
		$tipo_oggetto=$_POST["tipo_oggetto"];
		// 2. id oggetto lo devo cercare come quello più vicino al punto individuato sulla mappa
		$query2="SELECT * FROM segnalazioni.tipo_oggetti_rischio WHERE valido='t' AND id=".$tipo_oggetto.";";
      echo $query2;
      echo "<br>";
      $result2 = pg_query($conn, $query2);
      //echo $query1;    
      while($r2 = pg_fetch_assoc($result2)) { 
      	$campo_identificativo= $r2['campo_identificativo'];
      	$nome_tabella=$r2['nome_tabella'];
 		}
		$query_closest_object="select ".$campo_identificativo." as ident from ".$nome_tabella." order by st_distance(st_transform(geom,4326),".$geom.") limit 1;";
		echo $query_closest_object;
      echo "<br>";
      $result_closest = pg_query($conn, $query_closest_object);
      //echo $query1;    
      while($r_closest = pg_fetch_assoc($result_closest)) { 
      	$id_oggetto= $r_closest['ident'];
 		}
	}

if ($id_oggetto!=''){
	$query_oggetto="INSERT INTO segnalazioni.join_oggetto_rischio(
            id_segnalazione, id_tipo_oggetto, id_oggetto)
    VALUES (".$id.", ".$tipo_oggetto.",".$id_oggetto.");";
   $result_oggetto = pg_query($conn, $query_oggetto);
	echo $query_oggetto;
	echo "<br>";	
}

//exit;


$query="INSERT INTO segnalazioni.t_storico_segnalazioni_in_lavorazione(id_segnalazione_in_lavorazione, log_aggiornamento) VALUES (";
$query=$query."".$id_segnalazione_lav.",'Aggiunto oggetto a rischio a segnalazione ".$id."');";
echo $query;

//exit;
$result = pg_query($conn, $query);
echo "<br>";



$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('segnalazioni','".$operatore ."', 'Eliminato oggetto a rischio da segnalazione ".$id."');";
$result = pg_query($conn, $query_log);
echo "<br>";
echo $query_log;

//exit;
header("location: ../dettagli_segnalazione.php?id=".$id);


?>