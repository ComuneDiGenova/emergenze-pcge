<?php 

$subtitle='Nuovo Provvedimento Cautelare (Interdizione all\'accesso)';

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
			<form action="provvedimenti_cautelari/nuovo_pc.php" method="POST">
        <input type="hidden" name="nome_tabella_oggetto_rischio" id="hiddenField" value="geodb.sottopassi" />
			<input type="hidden" name="descrizione_oggetto_rischio" id="hiddenField" value="Sottopassi" />
			<input type="hidden" name="nome_campo_id_oggetto_rischio" id="hiddenField" value="id_crit" />
			
			


       
            
            


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
			$query2="SELECT * FROM segnalazioni.tipo_provvedimenti_cautelari WHERE id=2 ";
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
            <h4><i class="fa fa-map-marker-alt"></i> Cerca sottopasso:</h4> 


				<!--div class="form-group">
					<label for="nome"> Seleziona l'opzione che intendi usare per localizzare il sopralluogo </label> <font color="red">*</font><br>
					<label class="radio-inline"><input type="radio" name="georef" id="civico" required="">Tramite civico</label>
					<label class="radio-inline"><input type="radio" name="georef" id="mappa">Tramite mappa</label>
					<label class="radio-inline"><input type="radio" name="georef" id="coord">Con coordinate note</label>
				</div-->


				</div> 
            <div class="row">



				<div class="col-lg-6"> 
             <div class="form-group  ">
              <label for="via">Sottopasso:</label> <font color="red">*</font>
                            <select name="id_sottopasso" id="sottopassi-list" class="selectpicker show-tick form-control" data-live-search="true" required="">
                            <option value="">Seleziona il sottopasso</option>
            <?php            
            $query2="SELECT * From \"geodb\".\"sottopassi\";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                //$valore=  $r2['id_crit']. ";".$r2['nome_crit'];            
            ?>
                        
                    <option name="id_sottopasso" value="<?php echo $r2['id_crit'];?>" ><?php echo $r2['nome_crit'];?></option>
             <?php } ?>

             </select>            
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
				
				
            <!--div class="row">
								<div id="mapid" style="width: 100%; height: 600px;"></div>
            </div--> 
            <div class="row">

					   


            <button  type="submit" class="btn btn-primary">Assegna provvedimento cautelare</button>
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
