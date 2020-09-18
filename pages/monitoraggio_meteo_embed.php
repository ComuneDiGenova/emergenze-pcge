<?php


?>





<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<hr>
</div>
			
			<!--div class="col-xs-8 col-sm-8 col-md-8 col-lg-8"-->
			<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
			<h4>Aggiornamenti monitoraggio
            <?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_mon">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				<?php
				}
				?>	
			
			
			<!-- Modal reperibilità-->
				<div id="new_mon" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire aggiornamento meteo</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" enctype="multipart/form-data" action="report/nuovo_mon.php?id=<?php echo $id;?> " method="POST">
		
						<div class="form-group">
						<label for="data_inizio" >Data (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date100" required>
						<!--div class="input-group-addon" id="js-date" >
							<span class="glyphicon glyphicon-th"></span>
						</div-->
					</div> 
					
					<div class="form-group"-->

                <label for="ora_inizio"> Ora :</label> <font color="red">*</font>

              <div class="form-row">
   
   
    				<div class="form-group col-md-6">
                  <select class="form-control"  name="hh_start" required>
                  <option name="hh_start" value="" > Ora </option>
                    <?php 
                      $start_date = 0;
                      $end_date   = 24;
                      for( $j=$start_date; $j<=$end_date; $j++ ) {
                      	if($j<10) {
                        	echo '<option value="0'.$j.'">0'.$j.'</option>';
                        } else {
                        	echo '<option value="'.$j.'">'.$j.'</option>';
                        }
                      }
                    ?>
                  </select>
                  </div>	
                  
      				<div class="form-group col-md-6">
                  <select class="form-control"  name="mm_start" required>
                  <option name="mm_start" value="00" > 00 </option>
                    <?php 
                      $start_date = 0;
                      $end_date   = 59;
                      $incremento = 15; 
                      for( $j=$start_date; $j<=$end_date; $j+=$incremento) {
                      	if($j<10) {
                        	echo '<option value="0'.$j.'">0'.$j.'</option>';
                        } else {
                        	echo '<option value="'.$j.'">'.$j.'</option>';
                        }
                      }
                    ?>
                  </select>
                  </div>                
                  
                </div>  
                </div>
					
					
					<div class="form-group">
				    <label for="aggiornamento">Aggiornamento</label> <font color="red">*</font>
				    <textarea class="form-control" id="aggiornamento" name="aggiornamento" rows="6" required></textarea>
				  </div>


               <!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->
					<div class="form-group">
					   <label for="note">Eventuali immagini allegate </label>
						<input type="file" class="form-control-file" accept="image/*" onchange="preview_images();"name="userfile[]" id="userfile" multiple>
					<br><div class="row" id="image_preview"></div>
					</div>



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci aggiornamento meteo</button>
            </form>

      </div>


      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>
			
			<?php
			$query='SELECT id, data, aggiornamento, allegati FROM report.t_aggiornamento_meteo
			WHERE id_evento = '.$id.';';
			//echo $query;
			$result = pg_query($conn, $query);
			while($r = pg_fetch_assoc($result)) {
				
				echo " <b>Aggiornamento meteo (".$r['data'].")</b>: ";
				echo $r['aggiornamento']."";
				//echo $r['allegati']."<br><br>";
				if ($r['allegati']!=''){
					$allegati=explode(";",$r['allegati']);
					// Count total files
					$countfiles = count($allegati);
					//echo $countfiles;
					// Looping all files
					if($countfiles > 0) {
						for($i=0;$i<$countfiles;$i++){
							$n_a=$i+1;
							echo ' <img class="img-responsive" src="../../'.$allegati[$i].'" alt="L\'allegato caricato non ha un formato immagine">';
							echo '<br> <button type="button" class="btn btn-info noprint"  data-toggle="modal" 
							data-target="#update_mon_'.$r['id'].'">
							<i class="fas fa-edit"></i> Edit </button>';
							echo ' - <a class="btn btn-info noprint" href="../../'.$allegati[$i].'"> Scarica allegato '.$n_a.'</a>';
						}
					}
				}
				echo "<br><hr>";
				?>
				
				<!-- Modal edit-->
				<div id="update_mon_<?php echo $r['id'];?>" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire aggiornamento meteo</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/update_mon.php?id=<?php echo $r['id'];?> " method="POST">
		
						<div class="form-group">
						<label for="data_inizio" >Data e ora (AAAA-MM-GG HH:MM) </label>                 
						<input type="text" class="form-control" name="data" id="data" readonly required 
						value="<?php echo $r['data'];?>" >
					</div> 
					
					
					
					
					<div class="form-group">
				    <label for="aggiornamento">Aggiornamento</label> <font color="red">*</font>
				    <div style="text-align: left;">
				    <textarea class="form-control" id="aggiornamento" 
				    name="aggiornamento" rows="6" required><?php echo $r['aggiornamento'];?></textarea> 
				    </div>
				  </div>
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Modifica aggiornamento meteo</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>
				
				
				<?php
				
				
				
			}
			?>

			</div>
			<!--div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
				<div class="panel panel-default">
					<div class="panel-heading noprint">
						<i class="fa fa-traffic-light fa-fw"></i> Mappa ufficiale <a target="_new" href="http://www.allertaliguria.gov.it">allertaliguria</a> 
						<div class="pull-right">
						</div>
					</div>
					<div class="panel-body">
							<img class="pull-right img-responsive" imageborder="0" alt="Problema di visualizzazione immagine causato da sito http://www.allertaliguria.gov.it/" src="https://mappe.comune.genova.it/allertaliguria/mappa_allerta_render.php">
					</div>                    
				</div>
                
                

            </div-->