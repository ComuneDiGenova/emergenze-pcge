<?php


function filtro($idfilter){
	//echo $idfilter."<br>";
	#############################################################################################################################################
	#                                       CREAZIONE DEL FILTRO
	#leggo un codice binario di tante cifre quante sono le disabilità, per ognuna ho 0 se non voglio visualizzarla e 1 se voglio visualizzarla
	#'00100000000000'
	#echo $idfilter[1];
	#contatore
	$number = strlen($idfilter);
	
	$filter = '';
	
	$check=0; #controllo se c'è almeno un uno
	for ($mul = 0; $mul <= $number; ++$mul) {
	    
	    if ($idfilter[$mul]==1) {
	        $check=1;
	    } 
	} 
	
	if ($check==1) {
	    $filter = $filter . ' WHERE  ' ;    
	}
	
	$check_first=1; // controllo sul primo
	for ($mul = 0; $mul <= $number; ++$mul) {
	    $mul2=$mul+1;
	
	    if ($idfilter[$mul]==1) {
	    		if($check_first==1){
	    			$filter = $filter . ' id_criticita='. $mul2;
	    		} else {
	    			$filter = $filter . ' OR id_criticita='. $mul2;
	    		}
	    		$check_first=0;    
	    } 
	} 
	
	//$filter = $filter . ')';
	//echo $filter;
	return $filter;
	return $number;
	#############################################################################################################################################
}




function filtro2($idfilter, $idfilter1, $idfilter2, $idfilter3){
	//echo $idfilter."<br>";
	#############################################################################################################################################
	#                                       CREAZIONE DEL FILTRO
	#leggo un codice binario di tante cifre quante sono i municipi, per ognuna ho 0 se non voglio visualizzarla e 1 se voglio visualizzarla
	#'00100000000000'
	#echo $idfilter[1];
	#contatore
	include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';


	$filter = '';

	$check=0; #controllo se c'è almeno un uno
	$check_s=0;
	$check_m=0;
	
	$number = strlen($idfilter);
	for ($mul = 0; $mul <= $number; ++$mul) {
	    
	    if ($idfilter[$mul]==1) {
	        $check=1;
			$check_s=1;
	    }
	} 
	
	$number1 = strlen($idfilter1);
	for ($mul = 0; $mul <= $number1; ++$mul) {
	    
	    if ($idfilter1[$mul]==1) {
	        $check=1;
			$check_m=1;
	    }
	} 
	
	
	
	if ($check==1) {
	    $filter = $filter . ' WHERE  ' ;    
	}
	
	
	
	if($check_s==1) {
		$filter = $filter . ' ( ' ;
		$check_first=1; // controllo sul primo
		for ($mul = 0; $mul <= $number; ++$mul) {
			$mul2=$mul+1;
		
			if ($idfilter[$mul]==1) {
					if($check_first==1){
						$filter = $filter . ' id_criticita='. $mul2;
					} else {
						$filter = $filter . ' OR id_criticita='. $mul2;
					}
					$check_first=0;    
			} 
		} 
		$filter = $filter . ' ) ' ;
	}
	if($check_s==1 and $check_m==1 ) {
		$filter = $filter . ' AND ' ;
	}
	
	if($check_m==1) {
		$filter = $filter . ' ( ' ;
		$check_first=1; // controllo sul primo
		for ($mul = 0; $mul <= $number1; ++$mul) {
			$mul2=$mul+1;
		
			if ($idfilter1[$mul]==1) {
					if($check_first==1){
						$filter = $filter . ' id_municipio='. $mul2;
					} else {
						$filter = $filter . ' OR id_municipio='. $mul2;
					}
					$check_first=0;    
			} 
		} 
		$filter = $filter . ' ) ' ;
	}
	
	
	
	//creo i riepiloghi per la pag. web
	if($check_s==1) {
		$el_filtri_s='(';
		$check_first=1; // controllo sul primo
		for ($mul = 0; $mul <= $number; ++$mul) {
			$mul2=$mul+1;
		
			if ($idfilter[$mul]==1) {
				$query_d= "select descrizione from segnalazioni.tipo_criticita where id=".$mul2.";";
				$result_d = pg_query($conn, $query_d);
				#echo $query_d;
				#exit;
				//$el_filtri_s = $el_filtri_s.' - '.$query_d;
				while($r_d = pg_fetch_assoc($result_d)) {			
					if($check_first==1){
						$el_filtri_s = $el_filtri_s.' '.$r_d['descrizione'];
					} else {
						$el_filtri_s = $el_filtri_s.' - '.$r_d['descrizione'];
					}
					$check_first=0; 
				}
			} 
		} 
		$el_filtri_s = $el_filtri_s.')';
	}

	
	if($check_m==1) {
		$el_filtri_m='(';
		$check_first=1; // controllo sul primo
		for ($mul = 0; $mul <= $number1; ++$mul) {
			$mul2=$mul+1;
		
			if ($idfilter1[$mul]==1) {
				$query_d= "select nome_munic from geodb.municipi where id='".$mul2."';";
				$result_d = pg_query($conn, $query_d);
				#echo $query_d;
				#exit;
				//$el_filtri_s = $el_filtri_s.' - '.$query_d;
				while($r_d = pg_fetch_assoc($result_d)) {			
					if($check_first==1){
						$el_filtri_m = $el_filtri_m.' '.$r_d['nome_munic'];
					} else {
						$el_filtri_m = $el_filtri_m.' - '.$r_d['nome_munic'];
					}
					$check_first=0; 
				}
			} 
		} 
		$el_filtri_m = $el_filtri_m.')';
	}
	
	//echo strlen($idfilter2)."<br>";
	//echo strlen($idfilter3)."<br>";
	$check2=0;
	if (strlen($idfilter2)>=12 || strlen($idfilter3)>=12){
		$check2=1;
	}
	
	if ($check==1 && $check2==1) {
	    $filter = $filter . " AND  " ;
	} else if ($check==0 && $check2==1) {
	    $filter = $filter . " WHERE  " ;
	}
	
	
	
	if ($check2==1) {
		$filter = $filter . " (" ;
	}
	
	if (strlen($idfilter2)>=12 ) {
		$filter = $filter . " data_ora > '".$idfilter2."' ";
	}
	
	if (strlen($idfilter2)>=12 && strlen($idfilter3)>=12) {
		$filter = $filter . " AND " ;
	}
	
	if (strlen($idfilter3)>=12) {
		$filter = $filter . " data_ora < '".$idfilter3."' ";
	}
	
	if ($check2==1){
		$filter = $filter . ")" ;
	}
	
	
	
	//$filter = $filter . ')';
	//echo $filter;
	
	return array($filter,$check_s, $check_m,$el_filtri_s,$el_filtri_m);
	#############################################################################################################################################
}
?>