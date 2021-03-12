<?php 

$subtitle="Mappa segnalazioni a schermo intero"

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto" >

<link rel="stylesheet" href="l_map/css/L.Control.Locate.min.css">
   <link rel="stylesheetl_map/" href="l_map/css/qgis2web.css">
   <link rel="stylesheet" href="l_map/css/MarkerCluster.css">
   <link rel="stylesheet" href="l_map/css/MarkerCluster.Default.css">
   <link rel="stylesheet" href="l_map/css/leaflet-measure.css">
   <link rel="stylesheet" href="../vendor//leaflet-search/src/leaflet-search.css">


    <title>Gestione emergenze</title>
<?php 
require('./req.php');

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

require('./check_evento.php');
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
            
            <!-- /.row -->
            
            <!--div class="row"-->
            <!--div class="embed-responsive embed-responsive-16by9">
				  <iframe class="embed-responsive-item" src="./mappa_leaflet.php"></iframe>
				</div-->

						<div id="map" style="width: 100%; height: 90vh;">
						</div>
						


					<!--iframe style="width:100%;height:100%;position:relative" src="./mappa_leaflet.php"></iframe-->
            <!--/div-->
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');

include './mappa_leaflet_embedded_meteo.php';							


?>

<script type="text/javascript" >

map.scrollWheelZoom.enable();
</script>
    

</body>

</html>
