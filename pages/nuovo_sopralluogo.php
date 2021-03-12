<?php 

$subtitle="Nuovo presidio"

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

if($profilo_sistema==8 and $uo_inc=='uo_1'){
	$profilo_sistema=3;
}

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
			<form action="sopralluoghi/nuovo_sopralluogo2.php" method="POST">
        
<div class="row"> 

            <h4><i class="fas fa-pencil-ruler"></i> Descrizione presidio</h4> 
            <div class="form-group col-md-3">
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
                                  <small id="eventohelp" class="form-text text-muted">Un solo evento attivo (per trasparenza lo mostriamo ma possiamo anche decidere di non farlo).</small>
             
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
             <div class="form-group col-md-3">
             <label for="id_civico">Seleziona squadra:</label> <font color="red">*</font>
					<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
					<option  id="uo" name="uo" value="">Seleziona la squadra</option>
					<?php
					
					$query2="SELECT * FROM users.v_squadre WHERE id_stato=2 AND num_componenti > 0 and profilo = '".$profilo_squadre."' ORDER BY nome;";
					$result2 = pg_query($conn, $query2);
					 
					while($r2 = pg_fetch_assoc($result2)) { 
						$valore=  $r2['cf']. ";".$r2['nome'];            
					?>
								
							<option id="uo" name="uo" value="<?php echo $r2['id'];?>" ><?php echo $r2['nome'].' ('.$r2['id'].')';?></option>
					 <?php } ?>
				</select>
				<small> Se non trovi una squadra adatta vai alla <a href="gestione_squadre.php" >gestione squadre</a>. </small>
             </div>
             
             
             
      
             
             
             
            <div class="form-group col-md-6">
                <label for="descrizione"> Descrizione</label> <font color="red">*</font>
                <input type="text" name="descrizione" class="form-control" required="">
             </div>

				</div> 
 				<hr>

       <div class="row"> 
				
				
				<div class="form-group col-md-12">
					<input type="checkbox" class="form-check-input" name="permanente" id="permanente">
						<label class="form-check-label" for="permanente">Accettazione automatica e immediata <br>
						</label>
						<br>
						<small>Con questo flag non sarà necessario accettare il presidio mobile. La squadra si considererà automaticamente sul posto. 
						Cliccare solo se la squadra sta effettivamente iniziando il presidio.
						</small>           
				</div>
				</div> 
 				<hr>
				<div class="row"> 

            <h4><i class="fa fa-map-marker-alt"></i> Geolocalizzazione:</h4> 


				<div class="form-group">
					<label for="nome"> Seleziona l'opzione che intendi usare per localizzare il presidio </label> <font color="red">*</font><br>
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


				</div> <!-- Chiudo col-md-6-->
				<div class="col-md-6"> 
				
	

				
					<div class="form-group">
                <label for="lat"> Latitudine </label> <font color="red">*</font>
                <input disabled="" type="text" name="lat" id="lat" class="form-control" required="">
              </div>
					
					<div class="form-group">
                <label for="lon"> Longitudine </label> <font color="red">*</font>
                <input disabled="" type="text" name="lon" id="lon" class="form-control" required="">
              </div>
					
				
				</div> <!-- Chiudo col-md-6-->
				
				</div> 
            <div class="row">
								<div id="mapid" style="width: 100%; height: 600px;"></div>
            </div> 
            <div class="row">

					<hr>
               <!--h4><i class="fa fa-plus"></i> Altro:</h4-->      


            <button  type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Cliccando su questo tasto confermi le informazioni precedenti e assegni il presidio alla squadra specificata">Assegna presidio</button>
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


    

</body>

</html>
