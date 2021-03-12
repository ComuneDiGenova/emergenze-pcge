<?php 
session_start();
//require('../validate_input.php');;


$id=$_GET["id"];
$lat=$_GET["lat"];
$lon=$_GET["lon"];
$zoom=$_GET["z"];
$subtitle="Sposta segnalazione n. " .$id;


$query= "SELECT note FROM segnalazioni.t_segnalazioni WHERE id=".$id.";";
$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$old_note=$r["note"];
	echo $old_note;
}



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

$page=basename($_SERVER['PHP_SELF']);

if ($profilo_sistema > 8){
	header("location: ./divieto_accesso.php");
}
?>

   <link rel="stylesheet" href="../vendor/leaflet-search/src/leaflet-search.css">
   
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

                <!--div class="col-lg-12">
                    <h1 class="page-header">Titolo pagina</h1>
                </div-->
                <!-- /.col-lg-12 -->
			<form action="segnalazioni/sposta_segnalazione.php?id=<?php echo $id; ?>" method="POST">
        


       
           
            
            <div class="row">
            <h4><i class="fa fa-map-marker-alt"></i> Modifica geolocalizzazione:</h4> 


				<div class="form-group">
					<label for="nome"> Seleziona l'opzione che intendi usare per georeferenziare la segnalazione (di default sposta il puntatore sulla mappa)</label> <font color="red">*</font><br>
					<label class="radio-inline"><input type="radio" name="georef" id="civico" required="">Tramite civico</label>
					<label class="radio-inline"><input type="radio" name="georef" id="mappa">Tramite mappa</label>
					<label class="radio-inline"><input type="radio" name="georef" id="coord">Con coordinate note</label>
				</div>


				</div> 
            <div class="row">
            
            
            <script>
            function getCivico(val) {
	            $.ajax({
	            type: "POST",
	            url: "get_civico.php",
	            data:'cod='+val,
	            success: function(data){
		            $("#civico-list").html(data);
	            }
	            });
            }

            </script>



				<div class="col-md-6"> 
             <div class="form-group  ">
              <label for="via">Via:</label> <font color="red">*</font>
                            <select disabled="" id="via-list" class="selectpicker show-tick form-control" data-live-search="true" onChange="getCivico(this.value);" required="">
                            <option value="">Seleziona la via</option>
            <?php            
            $query2="SELECT * From \"geodb\".\"m_vie_unite\";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                $valore=  $r2['codvia']. ";".$r2['desvia'];            
            ?>
                        
                    <option name="codvia" value="<?php echo $r2['codvia'];?>" ><?php echo $r2['desvia'];?></option>
             <?php } ?>

             </select>            
             </div>


            <div class="form-group">
              <label for="id_civico">Civico:</label> <font color="red">*</font>
                <select disabled="" class="form-control" name="id_civico" id="civico-list" class="demoInputBox" required="">
                <option value="">Seleziona il civico</option>
            </select>         
             </div>

				<!--div class="form-group">
					<label for="civrischio"> Il civico è in pericolo?</label><br>
					<label class="radio-inline"><input type="radio" name="civrischio" value="" checked>Non specificato</label>
					<label class="radio-inline"><input type="radio" name="civrischio" value="t">Sì</label>
					<label class="radio-inline"><input type="radio" name="civrischio"value="f">No</label>
				</div-->


				</div> <!-- Chiudo col-md-6-->
				<div class="col-md-6"> 
				
	

				
					<div class="form-group">
                <label for="lat"> Latitudine </label> <font color="red">*</font>
                <input type="text" name="lat" id="lat" class="form-control" value="<?php echo $lat;?>" required="">
              </div>
					
					<div class="form-group">
                <label for="lon"> Longitudine </label> <font color="red">*</font>
                <input type="text" name="lon" id="lon" class="form-control" value="<?php echo $lon;?>" required="">
              </div>
					
				
				
				</div> <!-- Chiudo col-md-6-->
				
				</div> 
            <div class="row">    

				<div class="panel-group">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title">
        <a data-toggle="collapse" href="#collapse1">Mappa</a>
      </h4>
    </div>
    <div id="collapse1" class="panel-collapse collapse show">
			<div id="mapid" style="width: 100%; height: 600px;"></div>
    </div>
  </div>
</div>



<!--script type="text/javascript" >
$('#collapse1').on('shown.bs.collapse', function (e) {
    mymap.invalidateSize(true);
});
</script-->
								
								
								       
				
            </div> 
            <div class="row">

					<hr>
               <h4><i class="fa fa-plus"></i> Altro:</h4>      
                    
                     
              <div class="form-group">
                <label for="note_geo"> Altre note utili alla localizzazione </label>
                <input type="text" name="note_geo" id="note_geo" class="form-control" >
                <small id="addrHelp" class="form-text text-muted" value="<?php echo $old_note;?>"> Qua è possibile specificare altre annotazioni, </small> 
              </div>

                             



            <button  type="submit" class="btn btn-primary">Invia modifiche alla segnalazione</button>
            </div>
            <!-- /.row -->
            

            </form>                
                
                
                
                
                

            <br><br>
            <div class="row">

            </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');

require('./mappa_georef.php');

?>

		

<script type="text/javascript" >

// con questa parte scritta in JQuery si evita che 
// l'uso del tasto enter abbia effetto sul submit del form

$(document).on("keydown", ":input:not(textarea)", function(event) {
    if (event.key == "Enter") {
        event.preventDefault();
    }
});



$('[type="radio"][id="mappa"]').prop("checked", true);

mymap.on('click', onMapClick);


var marker = L.marker([<?php echo $lat;?>, <?php echo $lon;?>],{}).addTo(mymap);


</script>



    

</body>

</html>
