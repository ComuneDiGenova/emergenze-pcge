<?php

$check_operatore=0;
$check_squadra=0;
$check_uo=0;


// check municipi
$id_profilo0=$id_profilo;
$test_municipi=count(explode('_',$id_profilo0));
if ($test_municipi==2){
	$id_profilo=explode('_',$id_profilo0)[0];
	$id_municipio=explode('_',$id_profilo0)[1];
} else {
	$id_profilo=$id_profilo0;
}


if(isset($id_uo)){
	if (substr($id_uo,0,3)=='com'){
		$uo_array=explode('_',$id_uo);
		$id_uo=$uo_array[1];
		$query_o="SELECT * FROM varie.t_incarichi_comune WHERE cod ='". $id_uo."';";
		//echo $query_o;
		$result_o=pg_query($conn, $query_o);
		while($r_o = pg_fetch_assoc($result_o)) {
				$id_uo_sistema=$r_o['profilo'];
				//echo $id_uo_sistema;
		}
		//echo $profilo_sistema;
	} else {
		$id_uo_sistema=8; //utenti esterni
		$uo_array=explode('_',$id_uo);
		$id_uo=$uo_array[1];
		$query_o="SELECT descrizione FROM users.uo_1_livello WHERE id1 ='". $id_uo."';";
		//echo $query_o;
		$result_o=pg_query($conn, $query_o);
		while($r_o = pg_fetch_assoc($result_o)) {
			$desc_livello1=$r_o['descrizione'];
			//echo $id_uo_sistema;
		}
	}
}



// per il comune bisogna togliere com_ e poi bisogna anche verificare gli incarichi esterni uo_1, etc.

 if( $profilo_sistema==$id_uo_sistema) {
	if ($profilo_sistema==8){
		if($desc_livello1==$livello1){
			$check_uo=1;
		}
	} else {
		$check_uo=1;
	}
	//echo "<h4><br><b>Incarico assegnato alla tua squadra!</b></h4>";
}


 if( $id_squadra==$id_squadra_operatore) {
	$check_squadra=1;
	//echo "<h4><br><b>Incarico assegnato alla tua squadra!</b></h4>";
}



if ($id_profilo<=3 and $id_profilo>0){
	if ($profilo_sistema<=3 and $profilo_sistema>0) {
		$check_operatore=1; 
	}
} else if($id_profilo==4) {
	if ($profilo_sistema==4) {
		$check_operatore=1; 
	}
} else if($id_profilo==5) {	
	if (substr($profilo_sistema,0,1) == $id_profilo and $profilo_cod_munic==$id_municipio){
		$check_operatore=1;
	}
} else if($id_profilo==6) {
	if (substr($profilo_sistema,0,1) == $id_profilo and $profilo_cod_munic==$id_municipio){ 
		$check_operatore=1;
	} else if (substr($profilo_sistema,0,1) == 3){
		$check_operatore=1;
	} else if (substr($profilo_sistema,0,1) == 2){
		// Richiesta Ticket #496: profili 2 e 3 devono essere in grado di lavorare-elaborare gli interventi provenienti da Verbatel.
		$check_operatore=1;
	}
} else if($id_profilo==7) {
	if (substr($profilo_sistema,0,1) == $id_profilo and $profilo_cod_munic==$id_municipio){ 
		$check_operatore=1;
	} 
}

//se amministratore di sistema può fare tutto!
if($profilo_sistema==1) {
	$check_operatore=1;
}

//echo 'check_operatore='.$check_operatore.'<br>';
//echo 'check_squadra='.$check_squadra.'<br>';
//echo 'check_uo='.$check_uo.'<br>';

if ($check_operatore==1 or $check_squadra==1 or $check_uo==1){
	echo ' <h3><i class="fas fa-user-check" style="color:#5fba7d"></i></h3>';
}

echo 'check_operatore: '.$check_operatore.'<br>';
echo 'id_profilo: '.$id_profilo.'<br>';
echo 'id_profilo0: '.$id_profilo0.'<br>';
echo 'profilo_sistema: '.$profilo_sistema.'<br>';
?>