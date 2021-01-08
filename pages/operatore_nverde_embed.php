<?php

?>


<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <hr>
            <h4>Operatore numero verde
            
            <?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_oNV">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_oNV" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire operatore Numero Verde</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/nuovo_oNV.php" method="POST">
		
			<?php
			$query2="SELECT matricola, cognome, nome, settore, ufficio FROM varie.v_dipendenti v
			ORDER BY cognome";
			//echo $query2;
			//$result2 = pg_query($conn, $query2);
			?>

				<div class="form-group  ">
				  <label for="cf">Seleziona dipendente comunale:</label> <font color="red">*</font>
								<select name="cf" id="cf" class="selectpicker show-tick form-control" data-live-search="true" required="">
								<option value="">Seleziona personale</option>
								<option value="NO_TURNO"><font color="red">TURNO VUOTO</font></option>
				<?php
				foreach ($arr as $result){
					echo '<option value="'.$result['matricola'].'">'.$result['cognome'].' '.$result['nome'].'('.$result['settore'].' - '.$result['ufficio'].')</option>';
				}
				?>
				 </select>            
				 </div>
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date9" required>
						<!--div class="input-group-addon" id="js-date" >
							<span class="glyphicon glyphicon-th"></span>
						</div-->
					</div> 
					
					<div class="form-group"-->

                <label for="ora_inizio"> Ora inizio:</label> <font color="red">*</font>

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
						<label for="data_fine" >Data fine (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_fine" id="js-date10" required>
						<!--div class="input-group-addon">
							<span class="glyphicon glyphicon-th"></span>
						</div-->
					</div> 
					
					<div class="form-group"-->

                <label for="ora_inizio"> Ora fine:</label> <font color="red">*</font>

              <div class="form-row">
   
   
    				<div class="form-group col-md-6">
                  <select class="form-control"  name="hh_end" required>
                  <option name="hh_end" value="" > Ora </option>
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
                  <select class="form-control"  name="mm_end" required>
                  <option name="mm_end" value="00" > 00 </option>
                    <?php 
                      $start_date = 59;
                      $end_date   = 59;
                      $incremento = 15;
                      for( $j=$start_date; $j<=$end_date; $j+=$incremento ) {
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci operatore Numero Verde</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>
				
				
				
				<?php
				}  else {
					echo "</h4>";
				}//else if($profilo_sistema <3) {
				
				
				
				
				
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end from report.t_operatore_nverde r ";
			$query = $query. "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
			if ($id != '') {
				//$query = $query. "where data_start < (select data_ora_chiusura FROM eventi.t_eventi where id =".$id.") and data_start > (select data_ora_inizio_evento FROM eventi.t_eventi where id =".$id.") ";
				$query = $query. "where (data_start < (select data_ora_chiusura FROM eventi.t_eventi where id =".$id.") OR (select data_ora_chiusura FROM eventi.t_eventi where id =".$id.") is null) and data_start > (select data_ora_inizio_evento FROM eventi.t_eventi where id =".$id.") ";

			} else {
				$query = $query. "where data_start < now() and data_end > now() ";
			}
			//$query = $query. "and data_end > now() ";
			//$query = $query. " and id1=".$r0["id1"]."";
			$query = $query. " order by data_start, cognome;";
			
			//echo $query;
			
			$check_reperibile=0;
			$result = pg_query($conn, $query);
			//echo "<ul>";
			while($r = pg_fetch_assoc($result)) { 
				$check_reperibile=1;
				//echo "<li>";
				echo "- ";
				if($r['cognome']==''){
					echo  "TURNO VUOTO - Dalle ";
				} else {
					echo  $r['cognome']." ".$r['nome']." - Dalle ";
				}
				//echo  $r['data_start']. " alle ".$r['data_end']. "<br>";
				echo date('H:i', strtotime($r['data_start'])). " del " .date('d-m-Y', strtotime($r['data_start']))." alle ";
				echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end'])). "<br>";
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono operatori del Numero Verde <br>';
			}
			
			echo "---.---.---<br>";
			//echo "</ul>";
			if ($id==''){
				$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end from report.t_operatore_nverde r ";
				$query = $query. "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
				$query = $query. "where data_start > now() ORDER by data_start;";
				//$query = $query. " and id1=".$r0["id1"]."";
				//$query = $query. " order by cognome;";
				
				//echo $query;
				
				$check_reperibile=0;
				$result = pg_query($conn, $query);
				//echo "<ul>";
				while($r = pg_fetch_assoc($result)) { 
					$check_reperibile=1;
					//echo "<li>";
					echo "- ";
					if($r['cognome']==''){
						echo  "TURNO VUOTO - Dalle ";
					} else {
						echo  $r['cognome']." ".$r['nome']." - Dalle ";
					}
					//echo  $r['data_start']. " alle ".$r['data_end']. "<br>";
					echo date('H:i', strtotime($r['data_start'])). " del " .date('d-m-Y', strtotime($r['data_start']))." alle ";
					echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end'])). "<br>";
					//echo "</li>";
				}
			}
			/*if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}*/   
         ?> 
            </div>