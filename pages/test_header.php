<?php

# chiamata alla funzione per la raccolta dei request headers 
$headers = getallheaders();
# visualizzazione dei valori dell'array tramite ciclo
foreach ($headers as $name => $content)
{
	# chiamata alla funzione per la raccolta dei request headers 
$headers = getallheaders();
# visualizzazione dei valori dell'array tramite ciclo


    echo "[$name] = $content<br>";
	if ($name=='comge_codicefiscale'){
		$CF=$content;
	}
	

}


echo "<br> CF: ". $CF;

?>