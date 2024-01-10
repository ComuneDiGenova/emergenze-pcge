<?php

if ($id_profilo<=3 and $id_profilo>0){
	echo "<h4><br><b>Centrale responsabile</b>: Centrale PC";
} else if($id_profilo==4) {
	echo "<h4><br><b>Centrale responsabile</b>: Centrale COA";
} else if($id_profilo==5) {
	echo "<h4><br><b>Centrale responsabile</b>: Municipio ".$id_municipio."";
} else if($id_profilo==6) {
	echo "<h4><br><b>Centrale responsabile</b>: Distretto ".$id_municipio."";
}

//echo $check_operatore;
if ($check_operatore==1 and $id_profilo==$profilo_sistema){
	echo ' ( <i class="fas fa-user-check" style="color:#5fba7d"></i> )';
}


?>