<?php 
// Start the session
session_start();
//$_SESSION['user']="MRZRRT84B01D969U";

$id=$_GET["id"];
$subtitle="Dettagli presidio n. ".$id;


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


$query_evento_aperto="SELECT s.id,
       e.valido 
		FROM segnalazioni.t_sopralluoghi s
		JOIN eventi.t_eventi e on e.id=s.id_evento
		WHERE s.id=".$id.";";

$result_e=pg_query($conn, $query_evento_aperto);
while($r_e = pg_fetch_assoc($result_e)) {
	if($r_e['valido']=='f') {
		$table='v_sopralluoghi_eventi_chiusi';
		//echo "false";
	} else {
		$table='v_sopralluoghi';
		//echo "true";
	}
}

?>
    
</head>

<body>

    <div id="wrapper">

        <?php 
            require('./navbar_up.php')
        ?>  
        <?php 
            require('./navbar_left.php')
        ?> 
            

        <div id="page-wrapper">
            <!--div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Titolo pagina</h1>
                </div>
            </div-->
            <!-- /.row -->
            
            <br><br>
            <div class="row">
            <div class="col-md-6">
				<?php
				$query= "SELECT *, st_x(st_transform(geom,4326)) as lon , st_y(st_transform(geom,4326)) as lat FROM segnalazioni.".$table." WHERE id=".$id." ORDER BY data_ora_stato DESC LIMIT 1;";
				//echo $query;
        
				$result=pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					$id_squadra_attiva=$r['id_squadra'];
					//$check_operatore=0;
					$id_squadra=$r['id_squadra'];
					$id_profilo=$r['id_profilo'];
					//echo $id_profilo;
					//echo "<br>";
					if($profilo_sistema==8 and $uo_inc=='uo_1' ){
						$profilo_sistema=3;
					}
					require('./check_operatore.php');
					            
            	
            	
               $query_s="SELECT a.id_squadra, to_char(a.data_ora,'YYYY-mm-dd HH24:MI') as data_ora, 
				to_char(a.data_ora_cambio, 'YYYY-mm-dd HH24:MI') as data_ora_cambio, to_char(c.time_stop,'YYYY-mm-dd HH24:MI') as time_stop,
				b.nome FROM segnalazioni.join_sopralluoghi_squadra a
				JOIN users.t_squadre b ON a.id_squadra=b.id 
				JOIN segnalazioni.t_sopralluoghi c ON c.id=a.id_sopralluogo";
				if($r["id_stato_sopralluogo"] < 3){
				?>
               <h4><br><b>Squadra</b>: <?php echo $r['descrizione_uo'];?>
			   
               <?php
			   if ($check_squadra==1){
						echo ' ( <i class="fas fa-user-check" style="color:#5fba7d"></i> )';
				}
				echo " </h4>";
				$query_s1=$query_s. " WHERE id_sopralluogo =".$id." and id_squadra=".$id_squadra_attiva." order by a.data_ora desc LIMIT 1;";
				$result_s1=pg_query($conn, $query_s1);
				while($r_s = pg_fetch_assoc($result_s1)) {
					if ($r_s['data_ora_cambio']!=''){
						$data_cambio=$r_s['data_ora_cambio'];
					} else if ($r_s['time_stop']!='') {
						$data_cambio=$r_s['time_stop'];
					} else {
						$data_cambio=date("Y-m-d H:i");
					}
					//echo "<li>Dalle ore ".$r_s['data_ora']." alle ore ".$data_cambio." squadra <b>".$r_s['nome']." </b><ul>";
					$query_ss="SELECT b.cognome, b.nome, a.capo_squadra, to_char(a.data_start, 'YYYY-mm-dd HH24:MI') as data_start, 
					to_char(a.data_end, 'YYYY-mm-dd HH24:MI') as data_end FROM users.t_componenti_squadre a
						JOIN varie.dipendenti_storici b ON a.matricola_cf = b.matricola  
						WHERE a.id_squadra = ".$r_s['id_squadra']. " and 
						((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
						(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
						UNION 
						SELECT b.cognome, b.nome, a.capo_squadra, to_char(a.data_start, 'YYYY-mm-dd HH24:MI') as data_start, 
						to_char(a.data_end, 'YYYY-mm-dd HH24:MI') as data_end FROM users.t_componenti_squadre a
						JOIN users.utenti_esterni b ON a.matricola_cf = b.cf 
						WHERE a.id_squadra = ".$r_s['id_squadra']. " and 
						((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
						(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
						UNION 
						SELECT b.cognome, b.nome, a.capo_squadra, to_char(a.data_start, 'YYYY-mm-dd HH24:MI') as data_start, 
						to_char(a.data_end, 'YYYY-mm-dd HH24:MI') as data_end  FROM users.t_componenti_squadre a
						JOIN users.utenti_esterni_eliminati b ON a.matricola_cf = b.cf 
						WHERE a.id_squadra = ".$r_s['id_squadra']. "  and 
						((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
						(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
						ORDER BY cognome";
						//echo $query_ss;
						$result_ss=pg_query($conn, $query_ss);
						while($r_ss = pg_fetch_assoc($result_ss)) {
							echo "<li>".$r_ss['cognome']." ".$r_ss['nome']." ";
							if ($r_ss['capo_squadra']=='t'){
								echo '(<i class="fas fa-user-tie" title="Capo squadra"></i>)';
							}
							echo ' da '.max($r_ss['data_start'], $r_s['data_ora']).' ';
							if ( $r_ss['data_end']!='' and $data_cambio!=''){
								echo 'alle ' .min($r_ss['data_end'],$data_cambio).'';
							} else {
								echo 'alle ' .$data_cambio.'';
							}
							echo "</li>";
						}
					
					echo "</ul><br>";
				
				}
			   	}
               
				
				$check_s0=0;
				$query_s0="SELECT a.data_ora FROM segnalazioni.join_sopralluoghi_squadra a
				WHERE id_sopralluogo =".$id.";";
				//echo $query_ss;
				$result_s0=pg_query($conn, $query_s0);
				while($r_s0 = pg_fetch_assoc($result_s0)) {
					$check_s=1;
				}
				
				
				if ($check_s==1){
				?>
				
				<button type="button" class="btn btn-success"  data-toggle="modal" data-target="#storico_s"><i class="fas fa-users"></i> Storico squadre </button>
				</h4>
				<!-- Modal storico_s-->
				<div id="storico_s" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
					<!-- Modal content-->
					<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Storico squadre</h4>
					</div>
					<div class="modal-body">
					<ul>
				<?php
				$query_s="SELECT a.id_squadra, a.data_ora, a.data_ora_cambio, c.time_stop, b.nome FROM segnalazioni.join_sopralluoghi_squadra a
				JOIN users.t_squadre b ON a.id_squadra=b.id 
				JOIN segnalazioni.t_sopralluoghi c ON c.id=a.id_sopralluogo
				WHERE id_sopralluogo =".$id." 
				ORDER BY data_ora";
				//echo $query_s;
				$result_s=pg_query($conn, $query_s);
				while($r_s = pg_fetch_assoc($result_s)) {
					if ($r_s['data_ora_cambio']!=''){
						$data_cambio=$r_s['data_ora_cambio'];
					} else if ($r_s['time_stop']!='') {
						$data_cambio=$r_s['time_stop'];
					} else {
						$data_cambio=date("Y-m-d H:i:s");
					}
					echo "<li>Dalle ore ".$r_s['data_ora']." alle ore ".$data_cambio." squadra <b>".$r_s['nome']." </b><ul>";
					$query_ss="SELECT b.cognome, b.nome, a.capo_squadra FROM users.t_componenti_squadre a
						JOIN varie.dipendenti_storici b ON a.matricola_cf = b.matricola  
						WHERE a.id_squadra = ".$r_s['id_squadra']. " and 
						((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
						(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
						UNION SELECT b.cognome, b.nome, a.capo_squadra FROM users.t_componenti_squadre a
						JOIN users.utenti_esterni b ON a.matricola_cf = b.cf 
						WHERE a.id_squadra = ".$r_s['id_squadra']. " and 
						((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
						(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
						UNION SELECT b.cognome, b.nome, a.capo_squadra FROM users.t_componenti_squadre a
						JOIN users.utenti_esterni_eliminati b ON a.matricola_cf = b.cf 
						WHERE a.id_squadra = ".$r_s['id_squadra']. "  and 
						((a.data_start < '".$r_s['data_ora']."' and (a.data_end > '".$r_s['data_ora']."' or a.data_end is null)) OR
						(a.data_start < '".$data_cambio."' and (a.data_end > '".$data_cambio."' or a.data_end is null)))
						ORDER BY cognome";
						//echo $query_ss;
						$result_ss=pg_query($conn, $query_ss);
						while($r_ss = pg_fetch_assoc($result_ss)) {
							echo "<li>".$r_ss['cognome']." ".$r_ss['nome']." ";
							if ($r_ss['capo_squadra']=='t'){
								echo '(<i class="fas fa-user-tie" title="Capo squadra"></i>)';
							}
							echo "</li>";
						}
					
					echo "</ul></li>";
				}
				?>
				</ul>
					</div>
				</div>
				</div>
				</div>
				<?php
				} else { echo '</h4>'; }//close if
				require('./check_responsabile.php');
				?>
               
			   
               <h4><br><b>Descrizione presidio</b>: <?php echo $r['descrizione']; ?></h4>
               <h4><br><b>Data e ora invio presidio</b>: <?php echo $r['data_ora_invio']; ?></h4>
               <?php //echo $id_squadra_attiva; ?>
               <hr>
            	
						
						<?php 
						$lon=$r['lon'];
						$lat=$r['lat'];
						$id_civico=$r['id_civico'];
						$geom=$r['geom'];
						$id_municipio=$r['id_municipio'];
						$zoom=16;
            		$id_lavorazione=$r['id_lavorazione'];
            		if ( $id_lavorazione>0){ 
            			$check_segnalazione=1;
            		} else {
            			$check_segnalazione=0;
            		}
					$id_evento=$r['id_evento'];
						echo "<h2>";
						//1;"Inviato ma non ancora preso in carico"
						//2;"Preso in carico"
						//3;"Chiuso"
						//4;"Rifiutato"
						
						$stato_attuale=$r["id_stato_sopralluogo"];
						if ($r["id_stato_sopralluogo"]==1){
							echo '<i class="fas fa-pause" style="color:orange"></i> ';
						} else if  ($r["id_stato_sopralluogo"]==2) {
							if ($r['time_start']!=null) {
								echo '<i class="fas fa-play" style="color:green"></i> ';
							} else {
								echo '<i class="fas fa-play" style="color:orange"></i> ';
							}
						} else if  ($r["id_stato_sopralluogo"]==3) {
							echo '<i class="fas fa-stop"></i> ';
						} else if  ($r["id_stato_sopralluogo"]==4) {
							echo '<i class="fas fa-times-circle"></i> ';
						}
						
						
						
						echo $r['descrizione_stato'];
						if ($r["parziale"]=='t'){
							echo '<br><br><i class="fas fa-battery-quarter"></i>  Presa in carico parziale';
						}
						echo "</h2><hr>";
						if ($r["id_stato_sopralluogo"]==1){
						?>
				      <div style="text-align: center;">
				      <?php 
				      	$check_mail=0; //check se ci sono mail a sistema
				      	$query2="SELECT mail FROM users.t_mail_squadre WHERE cod='".$r['id_squadra']."';";
							$result2=pg_query($conn, $query2);
							while($r2 = pg_fetch_assoc($result2)) {
							  $check_mail=1; //check se ci sono mail a sistema
							}
							if($check_mail==1 and $check_operatore==1) {
								//echo $r['id_squadra'];
								echo '<a class="btn btn-info" href="sopralluoghi/sollecito.php?id='.$id.'&u='.$r['id_squadra'].'"> <i class="fas fa-at"></i> Invia sollecito </a> ';
							
							}
						if ($check_squadra==1 or $check_operatore==1){
				      ?>
				      <button type="button" class="btn btn-success"  data-toggle="modal" data-target="#accetta"><i class="fas fa-thumbs-up"></i> Presa in carico</button>

						<?php } ?>
						<!--button type="button" class="btn btn-danger"  data-toggle="modal" data-target="#rifiuta"><i class="fas fa-thumbs-down"></i> Rifiuta (DEMO)</button-->
						</div>
						
						<!-- Modal accetta-->
						<div id="accetta" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Previsioni esecuzione presidio</h4>
						      </div>
						      <div class="modal-body">
						      
						
						   <form autocomplete="off" action="sopralluoghi/accetta.php?id=<?php echo $id; ?>" method="POST">
							<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
							<input type="hidden" name="squadra" value="<?php echo $r['id_squadra'];?>" />
							<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
							<!--input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" /-->
								
									 <div class="form-group">
						<label for="data_inizio" >Data prevista per eseguire il presidio (AAAA-MM-GG) </label>  <font color="red">*</font>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date" required>
						<!--div class="input-group-addon">
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
                      $incremento = 10; 
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
								
					<!--div class="form-group">		
							<div class="radio-inline">
							  <label><input type="radio" name="parziale" value='f' required="">Presa in carico regolare</label>
							</div>
							<div class="radio-inline">
							  <label><input type="radio" name="parziale" value='t'>Presa in carico parziale</label>
							</div>				
						</div-->		
								
						           <div class="form-group">
									    <label for="note">Note</label>
									    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
									  </div>    
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Prendi in carico presidio</button>
						            </form>
						
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
						      </div>
						    </div>
						
						  </div>
						</div>
						

						<!-- Modal rifiuta-->
						<div id="rifiuta" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Rifiuta presidio</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off" action="sopralluoghi/rifiuta.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
									<input type="hidden" name="squadra" value="<?php echo $r['id_squadra'];?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
										 <div class="form-group">
									    <label for="note_rifiuto">Note rifiuto</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note_rifiuto"  name="note_rifiuto" rows="3"></textarea>
									  </div>
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Rifiuta presidio</button>
						            </form>
						
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
						      </div>
						    </div>
						
						  </div>
						</div>
						
						
						<hr>
						
						<?php
							
							
							
						} else if ($r["id_stato_sopralluogo"]==2) {
							
							
							$check_richiesta_cambio=0;
							$query3="SELECT * FROM segnalazioni.t_sopralluoghi_richiesta_cambi WHERE id_sopralluogo=".$id." AND eseguito='f';";
							//echo $query3 . "<br>";
							$result3=pg_query($conn, $query3);
							while($r3 = pg_fetch_assoc($result3)) {
							  $check_richiesta_cambio=1; //check se ci sono richieste cambi
							}
							$query3="SELECT * FROM segnalazioni.t_sopralluoghi_richiesta_cambi WHERE id_sopralluogo=".$id." AND eseguito is null;";
							//echo $query3 . "<br>";
							$result3=pg_query($conn, $query3);
							while($r3 = pg_fetch_assoc($result3)) {
							  $check_richiesta_cambio=-1; //check se ci sono richieste cambi
							 
							}
							if($check_richiesta_cambio==0) {
								if ($check_squadra==1 or $check_operatore==1){
						?>
						
						
						   <a type="button" class="btn btn-warning"  href="./sopralluoghi/chiedi_cambio.php?id=<?php echo $id;?>&l=<?php echo $id_lavorazione;?>"><i class="fas fa-exchange-alt"></i> Richiesta di cambio squadra</a>
				      	<?php
				      }
				      } else if($check_richiesta_cambio==-1){
				      	?>
							<h4> Richiesta cambio in corso ( 
							<?php
							$querys="SELECT * FROM segnalazioni.join_sopralluoghi_squadra WHERE id_sopralluogo=".$id." and valido is null; ";
							//echo $querys;
							$results=pg_query($conn, $querys);
							while($rs = pg_fetch_assoc($results)) {
								$old_id = $rs['id_squadra'];
							}
							$results=pg_query($conn, $querys);
							while($rs = pg_fetch_assoc($results)) {
								echo $rs['nome']. " in uscita";
							}
							
							
							?>
							)</h4>
							<?php
							if ($check_squadra==1 or $check_operatore==1){
							?>
							<a type="button" class="btn btn-warning"  href="./sopralluoghi/cambio2.php?id=<?php echo $id;?>&os=<?php echo $old_id;?>"><i class="fas fa-exchange-alt"></i> Conferma che il cambio squadra<br>è stato portato a termine</a>

						<?php
						}
						 
				      } else {
				      	?>
				      	<div style="text-align: center;">
				      	<h3> <i class="fa fa-exclamation fa-fw" style="color:red"></i>
				      	Richiesto cambio squadra
				      	<i class="fa fa-exclamation fa-fw" style="color:red"></i>
				      	</h3>
				      	<?php 
							if ($check_operatore==1){
							?>
				      	<button type="button" class="btn btn-warning"  data-toggle="modal" data-target="#cambio"><i class="fas fa-exchange-alt"></i> Cambio squadra</button>
							<?php
							}
							?>
							</div>
						<!-- Modal incarico interno-->
						<div id="cambio" class="modal fade" role="dialog">
						  <div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Cambio squadra presidio</h4>
							  </div>
							  <div class="modal-body">
							  

								<form autocomplete="off" action="sopralluoghi/cambio.php?id=<?php echo $id; ?>&l=<?php echo $id_lavorazione; ?>" method="POST">
								<input type="hidden" name="uo_old" value="<?php echo $r['descrizione_uo'];?>" />
								<input type="hidden" name="id_squadra_old" value="<?php echo $r['id_squadra'];?>" />

									<?php
									$query2="SELECT * FROM users.v_squadre WHERE id_stato=2  AND num_componenti > 0 and profilo = '".$profilo_squadre."' ORDER BY nome ";
									$result2 = pg_query($conn, $query2);
									?>
									<div class="form-group">
									  <label for="id_civico">Seleziona squadra:</label> <font color="red">*</font>
										<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
										<option  id="uo" name="uo" value="">Seleziona la squadra</option>
										<?php    
										while($r2 = pg_fetch_assoc($result2)) { 
											$valore=  $r2['cf']. ";".$r2['nome'];            
										?>
													
												<option id="uo" name="uo" value="<?php echo $r2['id'];?>" ><?php echo $r2['nome'].' ('.$r2['id'].')';?></option>
										 <?php } ?>
									</select>
									<small> Se non trovi una squadra adatta vai alla <a href="gestione_squadre.php" >gestione squadre</a>. </small>
									 </div>

									<div class="form-group">
										<input type="checkbox" class="form-check-input" name="permanente" id="permanente" checked="">
											<label class="form-check-label" for="permanente">La squadra &egrave gi&agrave cambiata<br>
											</label>
											<br>
											<small>Togliere il flag se il cambio squadra fosse solo programmato ma non effettivo. 
											</small>           
									</div>									 
									
										  



								<button  id="conferma" type="submit" class="btn btn-primary">Cambia squadra</button>
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
						
							<h4><br><b>Ora prevista per eseguire il presidio</b>: <?php echo $r['time_preview']; ?></h4>
							<?php if ($r['time_start']==''){
								if ($check_squadra==1 or $check_operatore==1){
							?>
								<a class="btn btn-success" href="./sopralluoghi/start.php?id=<?php echo $id;?>"><i class="fas fa-play"></i> La squadra è sul posto </a><br><br>
							<?php 
								}
							} else { ?>
								<h4><br><b>Ora inizio esecuzione presidio</b>: <?php echo $r['time_start']; ?></h4>
							<?php } 
								if ($check_squadra==1 or $check_operatore==1){
							?>
							
							<button type="button" class="btn btn-danger"  data-toggle="modal" data-target="#chiudi"><i class="fas fa-stop"></i> Chiudi</button>
						
						<?php	
							}
						} else if ($r["id_stato_sopralluogo"]==3) {
						?>
							<h4><br><b>Ora prevista per eseguire il presidio</b>: <?php echo $r['time_preview']; ?></h4>
							<h4><br><b>Ora inizio esecuzione presidio</b>: 
							<?php 
							if($r['time_start']!=''){
								echo $r['time_start']; 
							} else {
								echo 'n.d (non in corso o avvio non inserito a sistema)';
							}
							?>
							</h4>
							<h4><br><b>Ora chiusura presidio</b>: <?php echo $r['time_stop']; ?></h4><hr>
							<h4><br><b>Note chiusura presidio</b>: <?php echo $r['note_ente']; ?></h4><hr>
						
						<?php	
						} else if ($r["id_stato_sopralluogo"]==4) {
						?>	
							<h4><br><b>Note rifiuto</b>: <?php echo $r['note_rifiuto']; ?></h4>
						<?php	
						}
					?>
					
					
					<!-- Modal rifiuta-->
						<div id="chiudi" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Chiudi presidio</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off" action="sopralluoghi/chiudi.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
									<input type="hidden" name="squadra" value="<?php echo $id_squadra_attiva;?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
										 <div class="form-group">
									    <label for="note_rifiuto">Note chiusura</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note_rifiuto"  name="note_rifiuto" rows="3"></textarea>
									  </div>
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Chiudi presidio</button>
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
					echo "<hr>";
					if ($check_segnalazione==1){
						include 'incarichi/panel_comunicazioni.php';
					} else{
						include 'sopralluoghi/panel_comunicazioni.php';
					}
					if ($stato_attuale<3){
					?>
					<div style="text-align: center;">
					<?php 
					if ($check_squadra==1 or $check_operatore==1){
					?>
					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_da_UO"><i class="fas fa-comment"></i> Invia comunicazione a Centrale</button>
					<?php }
					if ($check_operatore==1){ ?>
					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_a_UO"><i class="fas fa-comment"></i> Invia comunicazione a Squadra</button>
					<?php } ?>
					</div>
					
					<!-- Modal comunicazione da UO-->
						<div id="comunicazione_da_UO" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Comunicazione a centrale responsabile presidio</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off"  enctype="multipart/form-data"  action="sopralluoghi/comunicazione_da_UO.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
									<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
										 <div class="form-group">
									    <label for="note">Testo comunicazione</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note"  name="note" rows="3"></textarea>
									  </div>
									
									<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->
									<div class="form-group">
									   <label for="note">Eventuale allegato</label>
										<input type="file" class="form-control-file" name="userfile[]" id="userfile" multiple>
									</div>
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Invia comunicazione</button>
						            </form>
						
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
						      </div>
						    </div>
						
						  </div>
						</div>
					
					
					<!-- Modal comunicazione a UO-->
						<div id="comunicazione_a_UO"  class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Comunicazione a squadra responsabile presidio</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off"  enctype="multipart/form-data" action="sopralluoghi/comunicazione_a_UO.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $id_squadra_attiva;?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
									<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
										 <div class="form-group">
									    <label for="note">Testo comunicazione </label>
									    <textarea required="" class="form-control" id="note"  name="note" rows="3"></textarea>
									  </div>
									  
									<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->
									<div class="form-group">
									   <label for="note">Eventuale allegato</label>
										<input type="file" class="form-control-file" name="userfile[]" id="userfile" multiple>
									</div>
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Invia comunicazione e mail</button>
						            </form>
						
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
						      </div>
						    </div>
						
						  </div>
						</div>
					
					
					
					
					
					
					
					
					<hr>
					<?php
					}
					if ($check_segnalazione==1){
					?>
					<h3><i class="fas fa-list-ul"></i> Segnalazioni collegate al presidio </h3><br>

					<?php
					// fine $query che verifica lo stato
					$query= "SELECT * FROM segnalazioni.".$table." WHERE id=".$id." and id_stato_sopralluogo =".$stato_attuale."  ORDER BY id_segnalazione;";
					
					
					//echo $query
        
					$result=pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) {
						//echo '<b>Unità operativa</b>: '.$r['descrizione_uo'];
						
						
					?>
						
						
									<div class="panel-group">
									  <div class="panel panel-info">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#segnalazione_<?php echo $r["id_segnalazione"];?>"><i class="fas fa-map-marker-alt"></i> Segnalazione n. <?php echo $r['id_segnalazione'];?> </a>
									      </h4>
									    </div>
									    <div id="segnalazione_<?php echo $r["id_segnalazione"];?>" class="panel-collapse collapse">
									      <div class="panel-body"-->
										<?php
										if($r['rischio'] =='t') {
											echo '<i class="fas fa-circle fa-1x" style="color:#ff0000"></i> Persona a rischio';
										} else if ($r['rischio'] =='f') {
											echo '<i class="fas fa-circle fa-1x" style="color:#008000"></i> Non ci sono persone a rischio';
										} else {
											echo '<i class="fas fa-circle fa-1x" style="color:#ffd800"></i> Non è specificato se ci siano persone a rischio';
										}
										?>
										<!--h4><i class="fas fa-list-ul"></i> Generalità </h4-->
										<br><b>Descrizione</b>: <?php echo $r['descrizione_segnalazione']; ?>
										<br><b>Tipologia</b>: <?php echo $r['criticita']; ?>
										<br> <a class="btn btn-info" href="./dettagli_segnalazione.php?id=<?php echo $r['id_segnalazione']; ?>" > Vai alla pagina della segnalazione </a>
										<hr>
										<?php
										$id_segnalazione=$r['id_segnalazione'];
										include './segnalazioni/section_oggetto_rischio.php';
										?>
										
							
							
										
									
									
												</div>
									    </div>
									  </div>
									</div>
						
						<a class="btn btn-info" href="dettagli_segnalazione.php?id=<?php echo $r["id_segnalazione"];?>"><i class="fas fa-undo"></i> Torna alla segnalazione <?php echo $r["id_segnalazione"];?></a>
						<br><br>
						<?php
						
						
						} // chiudi if
					}
					
						$no_segn=1; //non sono nella pagina della segnalazione--> disegno marker
						$zoom=16;
					
					?>
						
						<br>
						
						<br>
						</div> 
						<div class="col-md-6">
						<h4> <i class="fas fa-map-marker-alt"></i> Indirizzo </h4>
						
						<?php
						require('./indirizzo_embedded.php');
						?>
						<h4> <i class="fas fa-map-marked-alt"></i> Mappa </h4>
						<!--div id="map_dettaglio" style="width: 100%; padding-top: 100%;"></div-->
						
						<div id="map" style="width: 100%; padding-top: 100%;">
						</div>
						
						<!--div style="width: 100%; padding-top: 100%;"-->
							<!--iframe class="embed-responsive-item" style="width:100%; padding-top:0%; height:600px;" src="./mappa_leaflet.php#16/<?php echo $lat;?>/<?php echo $lon;?>"></iframe-->
						<!--/div-->
						<hr>
						
						</div>
			
					


            </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->

<?php 


require('./req_bottom.php');
$id_segnalazione=$id;

include './mappa_leaflet_embedded.php';


//}





require('./footer.php');
?>




<script type="text/javascript" >

$('input[type=radio][name=invio]').attr('disabled', true);

(function ($) {
    'use strict';
    
    
    $('[type="radio"][name="risolta"][value="f"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('input[type=radio][name=invio]').removeAttr('disabled');
            return true;
        }
    });
    
	$('[type="checkbox"][id="cat"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#conferma_chiudi').removeAttr('disabled');
            return true;
        }
        
    });
}(jQuery));





$(document).ready(function() {
    $('#js-date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
});


</script>  

</body>

</html>
