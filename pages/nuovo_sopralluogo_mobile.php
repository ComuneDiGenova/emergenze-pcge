<?php 

$subtitle="Nuovo presidio mobile"

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
			<form action="sopralluoghi/nuovo_sopralluogo_mobile2.php" method="POST">
        
<div class="row"> 

            <h4><i class="fas fa-pencil-ruler"></i> Descrizione presidio</h4> 
            <div class="form-group col-md-4">
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
             <div class="form-group col-md-4">
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
             
             
             
      
             
             
             
           <div class="form-group col-md-4">
             <label for="id_civico">Percorso:</label> <font color="red">*</font>
					<select class="form-control" name="percorso" id="percorso-list" class="demoInputBox" required="">
					<option  id="percorso" name="percorso" value="">Seleziona il presidio mobile</option>
					<?php
					
					$query2="SELECT p.percorso  FROM geodb.v_presidi_mobili p
							WHERE trim(p.percorso) NOT IN 
							(select  trim(descrizione)from segnalazioni.t_sopralluoghi_mobili WHERE time_stop is null)
							order by p.percorso; --by substring(p.percorso,1,1), substring(p.percorso,2,2)::int;";
					$result2 = pg_query($conn, $query2);
					 
					while($r2 = pg_fetch_assoc($result2)) { 
						//$valore=  $r2['percorso']. ";".$r2['percorso'];            
					?>
								
							<option id="percorso" name="percorso" value="<?php echo $r2['percorso'];?>" ><?php echo $r2['percorso'].' ('.$r2['percorso'].')';?></option>
					 <?php } ?>
				</select>
				<small> La definizione dei percorsi è gestita direttamente dalla Protezione Civile tramite funzionalità del geoportale</a>. </small>
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
				<div class="form-group col-md-12">
                <label for="descrizione"> Note</label> 
                <input type="text" name="descrizione" class="form-control" >
            </div>
       		</div> 
            
            



            <button  type="submit" class="btn btn-primary" data-toggle="tooltip" data-placement="top" title="Cliccando su questo tasto confermi le informazioni precedenti e assegni il presidio alla squadra specificata">Assegna presidio mobile</button>
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
