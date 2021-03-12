<?php 

$subtitle="Pagina monitoraggio meteo";

$id=$_GET['id'];


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
//require('./tables/griglia_dipendenti_save.php');
require('./req.php');
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');
//require('./conn.php');

require('./check_evento.php');


?>


<style type="text/css">
            
            .panel-allerta {
				  border-color: <?php echo $color_allerta; ?>;
				}
				.panel-allerta > .panel-heading {
				  border-color: <?php echo $color_allerta; ?>;
				  color: white;
				  background-color: <?php echo $color_allerta; ?>;
				}
				.panel-allerta > a {
				  color: <?php echo $color_allerta; ?>;
				}
				.panel-allerta > a:hover {
				  color: #337ab7;
				  /* <?php echo $color_allerta; ?>;*/
				}
            
            @media print
		   {
			  p.bodyText {font-family:georgia, times, serif;}
			  
			  .rows-print-as-pages .row {
				page-break-before: auto;
			  }
			  
			  
			   table,
				table tr td,
				table tr th {
					page-break-inside: avoid;
				}
			  .noprint
			  {
				display:none
			  }
			  
		   }
            
            
            </style>

    
</head>

<body>

    <div id="wrapper">

        <div id="navbar1">
<?php
require('navbar_up.php');
?>
</div>  
        <?php 
            require('./navbar_left.php');
            
         

        ?> 
            

        <div id="page-wrapper">
            <div class="row">
                <!--div class="col-sm-12">
                    <h1 class="page-header">Dashboard</h1>
                </div-->
                <!-- /.col-sm-12 -->
            </div>
            <!-- /.row -->
            
            
            <?php //echo $note_debug; ?>
           

            
            <div class="row">
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h3>Evento n. <?php echo $id; ?> - Tipo: 
			<?php
			$query_e='SELECT e.id, tt.descrizione 
            FROM eventi.t_eventi e
            JOIN eventi.join_tipo_evento t ON t.id_evento=e.id
            JOIN eventi.tipo_evento tt on tt.id=t.id_tipo_evento
			 	WHERE e.id =' .$id.';';
				$result_e = pg_query($conn, $query_e);
				while($r_e = pg_fetch_assoc($result_e)) {
					echo $r_e['descrizione'];
				}
			?>
			</h3>
			</div>
			</div>
			<hr>
			<div class="row">
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
				<?php if( $descrizione_allerta!= 'Nessuna allerta') {?>
					<h4> Allerta <?php echo $descrizione_allerta; ?> in corso 
					<em><i class="fas fa-circle fa-1x" style="color:<?php echo $color_allerta; ?>"></i></em>
					</h4>
				 <?php } else { ?>
					<h4> Nessuna allerta in corso <em><i class="fas fa-circle fa-1x" style="color:<?php echo $color_allerta; ?>"></i></em>
					</h4>
				 <?php }  ?> 
			</div>	
			
			<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
				<?php if( $descrizione_allerta!= 'Nessuna allerta') {?>
					<h4> Fase Operativa Comunale di <?php echo $descrizione_foc; ?> in corso 
					<em><i class="fas fa-circle fa-1x" style="color:<?php echo $color_foc; ?>"></i></em>
					</h4>
				 <?php } else { ?>
					<h4> Nessuna Fase Operativa Comunale in corso <em><i class="fas fa-circle fa-1x" style="color:<?php echo $color_foc; ?>"></i></em>
					</h4>
				 <?php }  ?> 
			</div>
			<hr>
			</div>
			
			<div class="row">
			 
			 <?php require('./monitoraggio_meteo_embed.php'); ?>
            
			</div>
			

            
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>

<script>

	/*var mymap = L.map('mapid').setView([44.411156, 8.932661], 12);

	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
			'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		id: 'mapbox.streets'
	}).addTo(mymap);

	L.marker([44.411156, 8.932661]).addTo(mymap)
		.bindPopup("<b>Hello world!</b><br />I am a leafletJS popup.").openPopup();




	var popup = L.popup();

	function onMapClick(e) {
		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(mymap);
	}

	mymap.on('click', onMapClick);*/



  
$(document).ready(function() {
   
    
    $('#js-date100').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    }); 
});




function printDiv(divName) {
     var printContents = document.getElementById(divName).innerHTML;
     var originalContents = document.body.innerHTML;

     document.body.innerHTML = printContents;

     window.print();

     document.body.innerHTML = originalContents;
}



</script>

<script type="text/javascript" src="./jquery.form.js"></script>

<script type="text/javascript" >
function preview_images() 
{
 var total_file=document.getElementById("userfile").files.length;
 //alert(total_file);
 for(var i=0;i<total_file;i++)
 {
 	if(event.target.files[i].type.indexOf("image")==-1){
   	alert("L'allegato caricato non è un immagine! Non verrà visualizzato.");
   }
		$('#image_preview').append("<div class='col-md-3'><img class='img-responsive' src='"+URL.createObjectURL(event.target.files[i])+"'></div>");
 }
}
</script>
    

</body>

</html>
