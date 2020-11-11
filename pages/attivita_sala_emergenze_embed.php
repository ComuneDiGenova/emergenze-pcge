<?php

?>

<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
<h3>Attività sala emergenze</h3>
</div>
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <hr><h4>Coordinatore di sala
            <?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_coord">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_coord" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire coordinatore sala</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/nuovo_coord.php" method="POST">
		
		<?php
					
			$query2="SELECT matricola, cognome, nome, settore, ufficio FROM varie.v_dipendenti v
			ORDER BY cognome";
			//echo $query2;
			$result2 = pg_query($conn, $query2);
			$arr = pg_fetch_all($result2);
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
						<input type="text" class="form-control" name="data_inizio" id="js-date" required>
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
						<input type="text" class="form-control" name="data_fine" id="js-date2" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci coordinatore sala</button>
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
				
				
				
				
				
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. "from report.t_coordinamento r ";
			$query = $query. " LEFT JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
			if ($id != '') {
				$query = $query. "where (data_start < (select data_ora_chiusura FROM eventi.t_eventi where id =".$id.") OR (select data_ora_chiusura FROM eventi.t_eventi where id =".$id.") is null) and data_start > (select data_ora_inizio_evento FROM eventi.t_eventi where id =".$id.") ";
			} else {
				$query = $query. "where data_start < now() and data_end > now() ";
			}
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
				echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono coordinatori<br>';
			}
			
			echo "---.---.---<br>";
			if ($id==''){
				$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
				$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
				$query = $query. " from report.t_coordinamento r ";
				$query = $query. "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
				$query = $query. "where data_start > now() ";
				//$query = $query. " and id1=".$r0["id1"]."";
				$query = $query. " order by data_start, cognome;";
				
				echo $query;
				
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
					echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				}
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
					
					
					//echo "</li>";
				}
			}
			/*if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono coordinatori<br>';
			}*/
			
			//echo "</ul>";
            
         ?>   
            </div>





            
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <hr>
            <h4>Operatore Monitoraggio Meteo
            
            <?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_mm">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_mm" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire responsabile Monitoraggio Meteo</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/nuovo_mm.php" method="POST">
		
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
				echo '<option value="'.$result['matricola'].'">'.$result['cognome'].' '.$result['nome'].' ('.$result['settore'].' - '.$result['ufficio'].')</option>';
			}
			?>
				 </select>            
				 </div>
			
            
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date3" required>
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
						<input type="text" class="form-control" name="data_fine" id="js-date4" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci responsabile Monitoraggio Meteo</button>
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
				
				
				
				
				
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. " from report.t_monitoraggio_meteo r ";
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
				echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
				
				
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}
			
			
			echo "---.---.---<br>";
			//echo "</ul>";
			if ($id==''){
				$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
				$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
				$query = $query. " from report.t_monitoraggio_meteo r ";
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
					echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
					
					
					//echo "</li>";
				}
			}
			/*if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}*/
            
         ?> 
            
            
            </div>


			</div>
			
			<div class="row">


            
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
            <hr>
            <h4>Operatore Presidi Territoriali
            
            <?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_pt">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_pt" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire responsabile Presidi Territoriali</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/nuovo_pt.php" method="POST">
		
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
						<input type="text" class="form-control" name="data_inizio" id="js-date5" required>
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
						<input type="text" class="form-control" name="data_fine" id="js-date6" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci responsabile Presidi Territoriali</button>
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
				
				
				
				
				
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. " from report.t_presidio_territoriale r ";
			$query = $query. "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
			if ($id != '') {
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
				echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
				
				
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono Responsabili Presidi Territoriali <br>';
			}
			
			
			echo "---.---.---<br>";
			
			if ($id==''){
				$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. " from report.t_presidio_territoriale r ";
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
					echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
					
					
					//echo "</li>";
				}
			}
			/*if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}*/
			//echo "</ul>";
            
         ?> 
            
            
            
            
            
            </div>






            
             <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
             <hr>
            <h4>Tecnico Protezione Civile

<?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_tPC">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_tPC" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire tecnico P.C.</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/nuovo_tPC.php" method="POST">
		
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
						<input type="text" class="form-control" name="data_inizio" id="js-date7" required>
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
						<input type="text" class="form-control" name="data_fine" id="js-date8" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci tecnico P.C.</button>
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
				
				
				
				
				
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. " from report.t_tecnico_pc r ";
			$query = $query. "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
			if ($id != '') {
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
				echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
				
				
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono tecnici di Protezione Civile <br>';
			}

			echo "---.---.---<br>";
			if ($id==''){
				$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. " from report.t_tecnico_pc r ";
				$query = $query. "LEFT JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
				$query = $query. "where data_start > now();";
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
					echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
					
					
					//echo "</li>";
				}
			}
			/*if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}*/
			//echo "</ul>";
            
         ?>             
            
            
            </div>
            
            
            <hr>
			</div>
			
			<div class="row">

<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
             <hr>
            <h4>Operatore Gestione Volontari

<?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_oV">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_oV" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire operatore Gestione Volontari P.C.</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/nuovo_oV.php" method="POST">
		
		<?php
					
			$query2="SELECT cf, cognome, nome, livello1 FROM users.v_utenti_esterni v WHERE (id1=1 or id1=8)
			UNION SELECT matricola as cf, cognome, nome, settore || ' - '|| ufficio as livello1 FROM varie.v_dipendenti
			ORDER BY cognome";
			//echo $query2;
			$result2 = pg_query($conn, $query2);
			$arr2 = pg_fetch_all($result2);
			//echo $query2;
			//$result2 = pg_query($conn, $query2);
		?>
		
			 <div class="form-group  ">
				  <label for="cf">Seleziona operatore tra i volontari (Gruppo Genova):</label> <font color="red">*</font>
				<select name="cf" id="cf" class="selectpicker show-tick form-control" data-live-search="true" required="">
				<option value="">Seleziona volontario </option>
				<option value="NO_TURNO"><font color="red">TURNO VUOTO</font></option>
				<?php
				foreach ($arr2 as $result2){
					echo '<option value="'.$result2["cf"].'">'.$result2["cognome"].' '.$result2["nome"].'('.$result2["livello1"].')</option>';
				}
				?>
				 </select>            
				 </div>
			
            
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date12" required>
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
						<input type="text" class="form-control" name="data_fine" id="js-date13" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci operatore gestione volontari</button>
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
				
				
				
				
				
			$query = "SELECT r.matricola_cf,";
			$query = $query. "case when u.cognome is not null then u.cognome ";
			$query = $query. "when d.cognome is not null then d.cognome end as cognome, ";
			$query = $query. "case when u.nome is not null then u.nome ";
			$query = $query. "when d.nome is not null then d.nome end  as nome, ";
			$query = $query. "r.data_start, r.data_end, warning_turno, modificato, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. " from report.t_operatore_volontari r ";
			$query = $query. "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
			$query = $query. "LEFT JOIN varie.dipendenti d ON r.matricola_cf=d.matricola ";
			if ($id != '') {
				$query = $query. "where (data_start < (select data_ora_chiusura FROM eventi.t_eventi where id =".$id.") OR (select data_ora_chiusura FROM eventi.t_eventi where id =".$id.") is null) and data_start > (select data_ora_inizio_evento FROM eventi.t_eventi where id =".$id.") ";
			} else {
				$query = $query. "where data_start < now() and data_end > now() ";
			}
			//$query = $query. "and data_end > now() ";
			//$query = $query. " and id1=".$r0["id1"]."";
			$query = $query. " order by data_start, cognome;";
			
			//echo $query."<br>";
			
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
				echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
				
				
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Volontariato <br>';
			}

			echo "---.---.---<br>";
			if ($id==''){
				//$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
				//$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
				//$query = $query. " from report.t_operatore_volontari r ";
				//$query = $query. "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
				$query = "SELECT r.matricola_cf,";
				$query = $query. "case when u.cognome is not null then u.cognome ";
				$query = $query. "when d.cognome is not null then d.cognome end as cognome, ";
				$query = $query. "case when u.nome is not null then u.nome ";
				$query = $query. "when d.nome is not null then d.nome end  as nome, ";
				$query = $query. "r.data_start, r.data_end, warning_turno, modificato, ";
				$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
				$query = $query. " from report.t_operatore_volontari r ";
				$query = $query. "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
				$query = $query. "LEFT JOIN varie.dipendenti d ON r.matricola_cf=d.matricola ";
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
					echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
					
					
					//echo "</li>";
				}
			}
			/*if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}*/
			//echo "</ul>";
            
         ?>             
            
            <hr>
            </div>


<div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
             <hr>
            <h4>Postazione presidio sanitario

<?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_anpas">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_anpas" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire operatore Postazione presidio sanitario</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="off" action="report/nuovo_anpas.php" method="POST">
		
		<?php
					
			$query2="SELECT cf, cognome, nome, livello1 FROM users.v_utenti_esterni v WHERE id1=1
			UNION SELECT matricola as cf, cognome, nome, settore || ' - '|| ufficio as livello1 FROM varie.v_dipendenti
			ORDER BY cognome";
			//echo $query2;
			$result2 = pg_query($conn, $query2);
			$arr2 = pg_fetch_all($result2);
			//echo $query2;
			//$result2 = pg_query($conn, $query2);
		?>
		
			 <div class="form-group  ">
				  <label for="cf">Seleziona operatore tra i volontari (Gruppo Genova e convenzionate):</label> <font color="red">*</font>
								<select name="cf" id="cf" class="selectpicker show-tick form-control" data-live-search="true" required="">
								<option value="">Seleziona volontario </option>
								<option value="NO_TURNO"><font color="red">TURNO VUOTO</font></option>
				<?php
				foreach ($arr2 as $result2){
					echo '<option value="'.$result2["cf"].'">'.$result2["cognome"].' '.$result2["nome"].'('.$result2["livello1"].')</option>';
				}
				?>
				 </select>            
				 </div>
			
            
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date14" required>
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
						<input type="text" class="form-control" name="data_fine" id="js-date15" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci operatore presidio sanitario</button>
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
				
				
				
				
				
			//$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
			//$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			//$query = $query. " from report.t_operatore_anpas r ";
			//$query = $query. "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
			$query = "SELECT r.matricola_cf,";
			$query = $query. "case when u.cognome is not null then u.cognome ";
			$query = $query. "when d.cognome is not null then d.cognome end as cognome, ";
			$query = $query. "case when u.nome is not null then u.nome ";
			$query = $query. "when d.nome is not null then d.nome end  as nome, ";
			$query = $query. "r.data_start, r.data_end, warning_turno, modificato, ";
			$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
			$query = $query. " from report.t_operatore_anpas r ";
			$query = $query. "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
			$query = $query. "LEFT JOIN varie.dipendenti d ON r.matricola_cf=d.matricola ";
			if ($id != '') {
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
				echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
				
				
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono operatori del presidio sanitario <br>';
			}

			echo "---.---.---<br>";
			if ($id==''){
				//$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end, warning_turno, modificato, ";
				//$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
				//$query = $query. " from report.t_operatore_anpas r ";
				//$query = $query. "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
				$query = "SELECT r.matricola_cf,";
				$query = $query. "case when u.cognome is not null then u.cognome ";
				$query = $query. "when d.cognome is not null then d.cognome end as cognome, ";
				$query = $query. "case when u.nome is not null then u.nome ";
				$query = $query. "when d.nome is not null then d.nome end  as nome, ";
				$query = $query. "r.data_start, r.data_end, warning_turno, modificato, ";
				$query = $query. "r.data_end-r.data_start > '10 hours' as warning_time ";
				$query = $query. " from report.t_operatore_anpas r ";
				$query = $query. "LEFT JOIN users.v_utenti_esterni u ON r.matricola_cf=u.cf ";
				$query = $query. "LEFT JOIN varie.dipendenti d ON r.matricola_cf=d.matricola ";
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
					echo date('H:i', strtotime($r['data_end'])). " del " .date('d-m-Y', strtotime($r['data_end']));
				
				if ($r['warning_turno']=='t'){
					echo '- <i class="fas fa-exclamation-triangle" style="color: orange;" title="Sovrapposizione con altri turni"></i>';
				} 
				if ($r['modificato']=='t'){
					echo '- <i class="fas fa-pencil-alt" style="color: red;" title="Turno modificato. Visualizzare i dettagli dallo storico turni"></i>';
				}
				if ($r['warning_time']=='t'){
					echo '- <i class="fas fa-exclamation-circle" style="color: orange;" title="Il turno di questa persona dura più di 10 h"></i>';
				}
				echo "<br>";
					
					
					//echo "</li>";
				}
			}
			/*if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}*/
			//echo "</ul>";
            
         ?>             
            
            <hr>
            </div>