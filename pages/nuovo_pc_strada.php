<?php 

$subtitle="Nuovo Provvedimento Cautelare (Chiusura strada)"

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


if ($profilo_sistema > 4){
	header("location: ./divieto_accesso.php");
}

?>

   <link rel="stylesheet" href="../vendor//leaflet-search/src/leaflet-search.css">
   
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
			<form action="provvedimenti_cautelari/nuovo_pc.php" method="POST">
			<input type="hidden" name="nome_tabella_oggetto_rischio" id="hiddenField" value="geodb.v_vie_unite" />
			<input type="hidden" name="descrizione_oggetto_rischio" id="hiddenField" value="Vie" />
			<input type="hidden" name="nome_campo_id_oggetto_rischio" id="hiddenField" value="codvia" />
			
			


       
            
            


            <div class="row"> 

            <h4><i class="fas fa-pencil-ruler"></i> Descrizione</h4> 
            <div class="form-group col-lg-2">
            <label for="nome"> Evento</label> <font color="red">*</font>  
 				<?php 
           $len=count($eventi_attivi);	               
				                
				if($len==1) {   
			   ?>


                <select readonly="" class="form-control"  name="evento" required>
                 
                    <?php 
                     for ($i=0;$i<$len;$i++){
                      
                        echo '<option name="evento" value="'.$tipo_eventi_attivi[0][0].'">'. $tipo_eventi_attivi[0][1].' (id='.$tipo_eventi_attivi[0][0].')</option>';
                      }
                    ?>
                  </select>
                                  <small id="eventohelp" class="form-text text-muted">Un solo evento attivo.</small>
             
            <?php } else {
            	?>

                  <select class="form-control"  name="evento" required>
                     <option value=''>Seleziona un evento tra quelli attivi </option>
                    <?php 
                     for ($i=0;$i<$len;$i++){
                      
                        echo '<option name="evento" value="'.$tipo_eventi_attivi[$i][0].'">'. $tipo_eventi_attivi[$i][1].' (id='.$tipo_eventi_attivi[$i][0].')</option>';
                      }
                    ?>
                  </select>

            	<?php
            	}
            	?>
              
            </div>
			<?php
			$query2="SELECT * FROM segnalazioni.tipo_provvedimenti_cautelari WHERE id=3 ";
			$result2 = pg_query($conn, $query2);
			?>
			<div class="form-group col-lg-3">
			  <label for="tipo_pc">Tipo provvedimento:</label> <font color="red">*</font>
				<select readonly="" class="form-control" name="tipo_pc" id="tipo_pc-list" class="demoInputBox" required="">
				<?php    
				while($r2 = pg_fetch_assoc($result2)) { 
					$valore=  $r2['id']. ";".$r2['descrizione'];            
				?>
							
						<option id="tipo_pc" name="tipo_pc" value="<?php echo $r2['id'];?>" ><?php echo $r2['descrizione'];?></option>
				 <?php } ?>
			</select>
			 </div> 
			
			
             <!--div class="form-group col-lg-3">
			 <label for="tipo">Tipologia di UO:</label> <font color="red">*</font>
				<select class="form-control" name="tipo" id="tipo" onChange="getUO2(this.value);"  required="">
				   <option name="tipo" value="" >  </option>
				<option name="tipo" value="direzioni" > Incarico a Direzioni (COC) </option>
				<option name="tipo" value="municipi" > Incarico a municipi </option>
				<option name="tipo" value="distretti" > Incarico a distretti di PM </option>
			</select>
			</div>
				 
							 <script>
				function getUO2(val) {
					$.ajax({
					type: "POST",
					url: "get_uo.php",
					data:'cod='+val,
					success: function(data){
						$("#uo-list-pc").html(data);
					}
					});
				}

				</script>

				 
				 
				<div class="form-group col-lg-4">
				  <label for="id_uo_pc">Seleziona l'Unità Operativa:</label> <font color="red">*</font>
					<select class="form-control" name="uo" id="uo-list-pc" class="demoInputBox" required="">
					<option value=""> ...</option>
				</select>         
				 </div-->  
             
			 
			 <?php
			$query3="SELECT * FROM users.tipo_origine_provvedimenti_cautelari";
			$result3 = pg_query($conn, $query3);
			?>
			 <div class="form-group col-lg-7">
				  <label for="id_uo_pc">Seleziona l'Unità Operativa che ha inviato il Provvedimento:</label> <font color="red">*</font>
					<select class="form-control" name="uo" id="uo" required="">
					<option value=""> ...</option>
					<?php    
				while($r3 = pg_fetch_assoc($result3)) { 
					$valore=  $r3['id']. ";".$r3['descrizione'];            
				?>
							
						<option id="tipo_pc" name="tipo_pc" value="<?php echo $r3['id'];?>" ><?php echo $r3['descrizione'];?></option>
				 <?php } ?>
			</select>
				</select>         
				 </div>
      
             
             
             
            <div class="form-group col-lg-12">
                <label for="descrizione"> Descrizione</label> <font color="red">*</font>
                <input type="text" name="descrizione" class="form-control" required="">
             </div>

				</div> 
 				<hr>
            <div class="row">
            <h4><i class="fa fa-map-marker-alt"></i> Localizzazione tramite civico:</h4> 


				<div class="form-group">
					<label for="nome"> Seleziona l'opzione che intendi usare per localizzare il sopralluogo </label> <font color="red">*</font><br>
					<label class="radio-inline"><input type="radio" name="georef" id="civico" required="">Via e civici</label>
					<label class="radio-inline"><input type="radio" name="georef" id="descrizione_via">Via e descrizione</label>
					<!--label class="radio-inline"><input type="radio" name="georef" id="coord">Con coordinate note</label-->
				</div>


				</div> 
            <div class="row">
            
            
            <script>
            function getCivico(val) {
	            $.ajax({
	            type: "POST",
	            url: "get_civico2.php",
	            data:'cod='+val,
	            success: function(data){
		            $("#civico-list").html(data);
					$("#civico-list2").html(data);
	            }
	            });
            }

            </script>



				<div class="col-lg-6"> 
			<h4><i class="fa fa-map-marker-alt"></i> Da civico:</h4> 
             <div class="form-group  ">
              <label for="via">Via:</label> <font color="red">*</font>
                            <select disabled="" name="codvia"  id="via-list" class="selectpicker show-tick form-control" data-live-search="true" onChange="getCivico(this.value);" required="">
                            <option value="">Seleziona la via</option>
            <?php            
            $query2="SELECT codvia, desvia From \"geodb\".\"m_vie_unite\";";
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
                <select disabled="" class="form-control" name="id_civico1" id="civico-list" class="demoInputBox story" onchange="change_map();" required="">
                <option value="">Seleziona il civico</option>
            </select>         
             </div>
			 
			<h4><i class="fa fa-map-marker-alt"></i> A civico:</h4> 
           
			 

            <div class="form-group">
              <label for="id_civico">Civico:</label> <font color="red">*</font>
                <select disabled="" class="form-control" name="id_civico2" id="civico-list2" class="demoInputBox story" onchange="change_map2();" required="">
                <option value="">Seleziona il civico</option>
            </select>         
             </div>
			 
			 
			 <h4><i class="fa fa-map-marker-alt"></i> Seleziona la via e descrivi il tratto da chiudere:</h4> 
			 
			 <div class="form-group  ">
              <label for="via">Via:</label> <font color="red">*</font>
                            <select disabled="" name="codvia" id="via-list2" class="selectpicker show-tick form-control" data-live-search="true" required="">
                            <option value="">Seleziona la via</option>
            <?php            
            $query2="SELECT  codvia, desvia From \"geodb\".\"m_vie_unite\";";
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
                <label for="desc_via"> Descrivi qua il tratto da chiudere </label> <font color="red">*</font>
                <input disabled="" type="text" name="desc_via" id="desc_via" class="form-control" required="">
             </div>
			 
			 
			 	<!--div class="form-group">
                <label for="lat"> Latitudine </label> <font color="red">*</font>
                <input disabled="" type="text" name="lat" id="lat" class="form-control" required="">
              </div>
					
					<div class="form-group">
                <label for="lon"> Longitudine </label> <font color="red">*</font>
                <input disabled="" type="text" name="lon" id="lon" class="form-control" required="">
              </div-->
			 

				</div> <!-- Chiudo col-lg-6-->
				<div class="col-lg-6">
				<div id="map" style="width: 100%; padding-top: 100%;">
						</div>

				</div> <!-- Chiudo col-lg-6-->
				
				
				<!--div class="col-lg-6"> 
				
	

				
					<div class="form-group">
                <label for="lat"> Latitudine </label> <font color="red">*</font>
                <input disabled="" type="text" name="lat" id="lat" class="form-control" required="">
              </div>
					
					<div class="form-group">
                <label for="lon"> Longitudine </label> <font color="red">*</font>
                <input disabled="" type="text" name="lon" id="lon" class="form-control" required="">
              </div>
					
				
				</div--> <!-- Chiudo col-lg-6-->
				
				</div> 
				
				
            <div class="row">
			
								<!--div id="mapid" style="width: 100%; height: 600px;"></div-->
            </div> 
            <div class="row">

					   


            <button type="submit" class="btn btn-primary">Assegna provvedimento cautelare</button>
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

include './mappa_leaflet_embedded.php';	


			

//require('./mappa_georef.php');

?>

<script>	

// onchange function (this format makes the function global)
 window.change_map = function(){
	 //alert('Fin qua Ok');
 
 		// Get the select element
    var e = document.getElementById("civico-list");
    // Get the value of the selected index
    var v = e.value;
    //alert(v)

    var latlng = v.split(',');
	//alert(latlng);
		var lat = latlng[0];
		var lng = latlng[1];
		var zoom = 16;

		// add a marker
		var marker = L.marker([lat, lng],{}).addTo(map);
		// set the view
		map.setView([lat, lng], zoom);
 }

 window.change_map2 = function(){
	 //alert('Fin qua Ok');
 
 		// Get the select element
    var e = document.getElementById("civico-list2");
    // Get the value of the selected index
    var v = e.value;
    //alert(v)

    var latlng = v.split(',');
	//alert(latlng);
		var lat = latlng[0];
		var lng = latlng[1];
		var zoom = 16;

		// add a marker
		var marker = L.marker([lat, lng],{}).addTo(map);
		// set the view
		map.setView([lat, lng], zoom);
 }


	// bind click event to the story divs, add a marker and zoom to that story's location. Remember to add dot before story as it is classname
	//$('.story').on('click', function(){
	/*(function ($) {	
		//$('[class="story"]').on('click', function(){
		$('.story').on('click', function(){
		alert('ok');
		
		
		// parse lat and lng from the divs data attribute
		var latlng = $(this).data().point.split(',');
		var lat = latlng[0];
		var lng = latlng[1];
		var zoom = 10;

		// add a marker
		var marker = L.marker([lat, lng],{}).addTo(map);
		// set the view
		map.setView([lat, lng], zoom);
	});
	});*/
	
	
	
	$('[type="radio"][id="civico"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#via-list').removeAttr('disabled');
            $('#via-list').selectpicker('refresh');
            $('#civico-list').removeAttr('disabled');
			$('#civico-list2').removeAttr('disabled');
			$('#desc_via').attr('disabled', true);
			//$('#lat').attr('disabled', true);
            //$('#lon').attr('disabled', true);
            //$('#lat').val('');
            //$('#lon').val('');
			$('#via-list2').attr('disabled', true);
			$('#via-list2').val('');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
	
	$('[type="radio"][id="descrizione_via"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#via-list2').removeAttr('disabled');
            $('#via-list2').selectpicker('refresh');
			$('#via-list').attr('disabled', true);
			$('#via-list').val('');
            $('#desc_via').removeAttr('disabled');
			//$('#lon').removeAttr('disabled');
			//$('#lat').removeAttr('disabled');
			$('#civico-list').attr('disabled', true);
			$('#civico-list2').attr('disabled', true);
            $('#civico-list').val('');
            $('#civico-lit2').val('');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
	
	
	
</script>
    

</body>

</html>
