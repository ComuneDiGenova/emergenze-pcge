<?php

$subtitle="Elenco soggetti vulnerabili";


$getfiltri=$_GET["f"];
$filtro_evento_attivo=$_GET["a"];
if(isset($_GET["from"])){
	$filtro_from=$_GET["from"];
}
if(isset($_GET["to"])){
	$filtro_to=$_GET["to"];
}
$resp=$_GET["r"];
$uo=$_GET["u"];



//echo $filtro_evento_attivo;


$uri=basename($_SERVER['REQUEST_URI']);
//echo $uri;

$pagina=basename($_SERVER['PHP_SELF']);


?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

    <title>Gestione emergenze</title>
<?php
require('./req.php');

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

require('./check_evento.php');

require('./tables/filtri_segnalazioni.php');
?>

</head>

<body>

    <div id="wrapper">

        <div id="navbar1">
<?php
require('navbar_up.php');
?>
</div>
        <?php
            require('./navbar_left.php')
        ?>


        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Elenco soggetti vulnerabili</h1>
                </div>
            </div>

	    <?php
	    	if ( $_SERVER['HTTP_HOST'] == 'vm-lxprotcivemet.comune.genova.it' ) {
		    $grid_url = "https://emergenze-apit.comune.genova.it/emergenze/soggettiVulnerabili/";
		} else {
		    $grid_url = "https://emergenze-api.comune.genova.it/emergenze/soggettiVulnerabili/";
		}
            ?>
            <div class="row">
							<table
							  id="table"
							  data-toggle="table"
							  data-show-refresh="true"
							  data-show-export="true"
							  data-pagination="false"
							  data-search="true"
							  data-url="<?php echo $grid_url?>"
							  data-pagination="false"
							  data-total-field="results"
							  data-data-field="result">
							  <thead>
							    <tr>
							      <th data-field="id">ID</th>
							      <th data-field="cognome">Cognome</th>
							      <th data-field="nome">Nome</th>
										<th data-field="indirizzo">Indirizzo</th>
										<th data-field="numero_civico">Civico</th>
										<th data-field="telefono">Telefono</th>
										<th data-field="gruppo">Gruppo</th>
										<th data-field="sorgente">Sorgente</th>
							    </tr>
							  </thead>
							</table>
            </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->



<?php

require('./footer.php');

require('./req_bottom.php');


?>



</body>



</html>
