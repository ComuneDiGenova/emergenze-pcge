<?php 
// Start the session
session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;
//$_SESSION['user']="MRZRRT84B01D969U";

$id=$_GET["id"];
$subtitle=" Dettagli incarico n. ".$id;


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



$check_evento_aperto=1;
$query_evento_aperto="SELECT e.valido
   FROM segnalazioni.join_segnalazioni_incarichi i
   JOIN segnalazioni.join_segnalazioni_in_lavorazione l ON i.id_segnalazione_in_lavorazione=l.id_segnalazione_in_lavorazione
	JOIN segnalazioni.t_segnalazioni s ON s.id=l.id_segnalazione
	JOIN eventi.t_eventi e on e.id=s.id_evento
	WHERE i.id_incarico=".$id.";";

//echo $query_evento_aperto;


$result_e=pg_query($conn, $query_evento_aperto);
while($r_e = pg_fetch_assoc($result_e)) {
	if($r_e['valido']=='f') {
		$check_evento_aperto=0;
		$table='v_incarichi_eventi_chiusi';
		//echo "false";
	} else {
		$table='v_incarichi';
		//echo "true";
	}
}



$check_evento_aperto=1;
$query_evento_aperto="SELECT e.valido FROM segnalazioni.join_incarico_provvedimenti_cautelari i 
     JOIN segnalazioni.v_provvedimenti_cautelari pc1 ON i.id_provvedimento = pc1.id
     JOIN eventi.t_eventi e ON e.id = pc1.id_evento
WHERE i.id_incarico=".$id.";";

//echo $query_evento_aperto;


$result_e=pg_query($conn, $query_evento_aperto);
while($r_e = pg_fetch_assoc($result_e)) {
	if($r_e['valido']=='f') {
		$check_evento_aperto=0;
		$table='v_incarichi_eventi_chiusi';
		//echo "false";
	} else {
		$table='v_incarichi';
		//echo "true";
	}
}


?>
    
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
           		$check_segnalazione=0;
					$result=pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) {
						//$id_squadra=$r['id_squadra'];
						$id_uo=$r['id_uo'];
						$id_profilo=$r['id_profilo'];
						
						
						if ($r['id_segnalazione']!=''){
							$check_segnalazione=1;
						}
						require('./check_operatore.php');
						//echo $id_profilo;
					?>            
            
               <h4><br><b>Unità operativa</b>: <?php echo $r['descrizione_uo']; ?>
               <?php
			   
               $uo_desc=$r['descrizione_uo'];
               if ($check_uo==1){
						echo ' ( <i class="fas fa-user-check" style="color:#5fba7d"></i> )';
				}
				require('./check_responsabile.php');
					               
               //require('./check_responsabile.php');
			   $descrizione_incarico=$r['descrizione'];
               ?>
               </h4>
			   
               <h4><br><b>Descrizione incarico</b>: <?php echo $r['descrizione']; ?></h4>
               <h4><br><b>Data e ora invio incarico</b>: <?php echo $r['data_ora_invio']; ?></h4>
               
               <hr>
            	
						
						<?php 
						$lon=$r['lon'];
						$lat=$r['lat'];
            		$id_lavorazione=$r['id_lavorazione'];
            		$id_civico=$r['id_civico'];
						$geom=$r['geom'];
						$id_municipio=$r['id_municipio'];
						$id_evento=$r['id_evento'];
						echo "<h2>";
						//1;"Inviato ma non ancora preso in carico"
						//2;"Preso in carico"
						//3;"Chiuso"
						//4;"Rifiutato"
						
						$stato_attuale=$r["id_stato_incarico"];
						if ($r["id_stato_incarico"]==1){
							echo '<i class="fas fa-pause" style="color:orange"></i> ';
						} else if  ($r["id_stato_incarico"]==2) {
							if ($r['time_start']!=null) {
								echo '<i class="fas fa-play" style="color:green"></i> ';
							} else {
								echo '<i class="fas fa-play" style="color:orange"></i> ';
							}
						} else if  ($r["id_stato_incarico"]==3) {
							echo '<i class="fas fa-stop"></i> ';
						} else if  ($r["id_stato_incarico"]==4) {
							echo '<i class="fas fa-times-circle"></i> ';
						}
						
						
						
						echo $r['descrizione_stato'];
						if ($r["parziale"]=='t'){
							echo '<br><br><i class="fas fa-battery-quarter"></i>  Presa in carico parziale';
						}
						echo "</h2><hr>";
						$id_uo_mail=$r['id_uo'];
						if ($r["id_stato_incarico"]==1){
						?>
				      <div style="text-align: center;">
					  
				      <?php 
						
				      	$check_mail=0; //check se ci sono mail a sistema
				      	$query2="SELECT mail FROM users.t_mail_incarichi WHERE cod='".$r['id_uo']."';";
							$result2=pg_query($conn, $query2);
							while($r2 = pg_fetch_assoc($result2)) {
							  $check_mail=1; //check se ci sono mail a sistema
							}
							if($check_mail==1 and $check_operatore==1) {
								
								echo '<a class="btn btn-info" href="incarichi/sollecito.php?id='.$id.'&u='.$r['id_uo'].'"> <i class="fas fa-at"></i> Invia sollecito </a> ';
							
							}
				      if ($check_uo==1 or $check_operatore==1){
				      ?>
				      <button type="button" class="btn btn-success"  data-toggle="modal" data-target="#accetta"><i class="fas fa-thumbs-up"></i> Presa in carico</button>

						<button type="button" class="btn btn-danger"  data-toggle="modal" data-target="#rifiuta"><i class="fas fa-thumbs-down"></i> Rifiuta</button>
						<?php } ?>
						</div>
						
						<!-- Modal accetta-->
						<div id="accetta" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Accetta incarico</h4>
						      </div>
						      <div class="modal-body">
						      
						
						   <form autocomplete="off" action="incarichi/accetta.php?id=<?php echo $id; ?>" method="POST">
							<input type="hidden" name="uo" value="<?php echo $uo_desc;?>" />
							<input type="hidden" name="id_lavorazione" value="<?php echo $id_lavorazione;?>" />
							<!--input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" /-->
								
									 <div class="form-group">
						<label for="data_inizio" >Data prevista per eseguire l'incarico (AAAA-MM-GG) </label>  <font color="red">*</font>                 
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
								
					<div class="form-group">		
							<div class="radio-inline">
							  <label><input type="radio" name="parziale" value='f' required="">Presa in carico regolare</label>
							</div>
							<div class="radio-inline">
							  <label><input type="radio" name="parziale" value='t'>Presa in carico parziale</label>
							</div>				
						</div>		
								
						           <div class="form-group">
									    <label for="note">Note</label>
									    <textarea class="form-control" id="note" name="note" rows="3"></textarea>
									  </div>    
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Accetta incarico</button>
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
						        <h4 class="modal-title">Rifiuta incarico</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off" action="incarichi/rifiuta.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $uo_desc;?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
										 <div class="form-group">
									    <label for="note_rifiuto">Note rifiuto</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note_rifiuto"  name="note_rifiuto" rows="3"></textarea>
									  </div>
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Rifiuta incarico</button>
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
							
							
							
						} else if ($r["id_stato_incarico"]==2) {
						?>
							<h4><br><b>Ora prevista per eseguire l'incarico</b>: <?php echo $r['time_preview']; ?></h4>
							<?php if ($r['time_start']==''){
								if ($check_uo==1 or $check_operatore==1){
								?>
							
								<a class="btn btn-success" href="./incarichi/start.php?id=<?php echo $id;?>"><i class="fas fa-play"></i> In esecuzione</a><br><br> 
								<?php 
								}
								} else { 
								?>
								<h4><br><b>Ora inizio esecuzione incarico</b>: <?php echo $r['time_start']; ?></h4>
							<?php } 
							if ($check_uo==1 or $check_operatore==1){
							?>
							<button type="button" class="btn btn-danger"  data-toggle="modal" data-target="#chiudi"><i class="fas fa-stop"></i> Chiudi</button>
						<?php	
						}
						} else if ($r["id_stato_incarico"]==3) {
						?>
							<h4><br><b>Ora prevista per eseguire l'incarico</b>: <?php echo $r['time_preview']; ?></h4>
							<h4><br><b>Ora inizio esecuzione incarico</b>: 
							<?php 
							if($r['time_start']!=''){
								echo $r['time_start']; 
							} else {
								echo 'n.d (non in corso o avvio non inserito a sistema)';
							}
							?>
							</h4>
							<h4><br><b>Ora chiusura incarico</b>: <?php echo $r['time_stop']; ?></h4><hr>
							<h4><br><b>Note chiusura incarico</b>: <?php echo $r['note_ente']; ?></h4><hr>
						
						<?php	
						} else if ($r["id_stato_incarico"]==4) {
						?>	
							<h4><br><b>Note rifiuto incarico</b>: <?php echo $r['note_rifiuto']; ?></h4><hr>
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
						        <h4 class="modal-title">Chiudi incarico</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off" action="incarichi/chiudi.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $uo_desc;?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
										 <div class="form-group">
									    <label for="note_rifiuto">Note chiusura</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note_rifiuto"  name="note_rifiuto" rows="3"></textarea>
									  </div>
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Chiudi incarico</button>
						            </form>
						
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
						      </div>
						    </div>
						
						  </div>
						</div>
					
					<?php

					
					//}
					echo "<hr>";
					include 'incarichi/panel_comunicazioni.php';
					if ($stato_attuale<3){
					?>
					<div style="text-align: center;">
					<?php 
					if ($check_uo==1 or $check_operatore==1){
					?>
					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_da_UO"><i class="fas fa-comment"></i> Invia comunicazione a Centrale</button>
					<?php }
					if ($check_operatore==1){
					?>
					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_a_UO"><i class="fas fa-comment"></i> Invia comunicazione a <?php echo $uo_desc; ?></button>
					<?php }
					?>
					</div>

					
					<?php
					if ($stato_attuale==2){
						
					?>
						<hr>
						<?php 
						//echo $profilo_sistema .' - '.$id_livello1;
						if ($profilo_sistema==8 and $id_livello1==$id_uo ){ ?>
						<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#mail"><i class="fas fa-comment"></i> Invia mail </button>
						<?php }?>
						- 
						<button class="btn btn-info noprint" onclick="printDiv('page-wrapper')">
               <i class="fa fa-print" aria-hidden="true"></i> Crea stampa o PDF 
			   <i class="fa fa-file-pdf" aria-hidden="true"></i></button>
					<?php }
					?>
					
					<!-- Modal comunicazione da UO-->
						<div id="comunicazione_da_UO" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Comunicazioni al responsabile segnalazione</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off"  enctype="multipart/form-data"  action="incarichi/comunicazione_da_UO.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $uo_desc;?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $id_lavorazione;?>" />
									<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
										 <div class="form-group">
									    <label for="note">Testo comunicazione</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note"  name="note" rows="3"></textarea>
									  </div>
									
									<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->
									<!--div class="form-group">
									   <label for="note">Eventuale allegato</label>
										<input type="file" class="form-control-file" name="userfile[]" id="userfile" multiple>
									</div-->
									<style type="text/css">
									#fileList_c > div > label > span:last-child {
										color: red;
										display: inline-block;
										margin-left: 7px;
										cursor: pointer;
									}
									#fileList_c input[type=file] {
										display: none;
									}
									#fileList_c > div:last-child > label {
										display: inline-block;
										width: 23px;
										height: 23px;
										font: 16px/22px Tahoma;
										color: orange;
										text-align: center;
										border: 2px solid orange;
										border-radius: 50%;
									}
									</style>

								<div class="form-group file">
								   <label for="note">Eventuali allegati</label>
								   <div id="fileList_c">
										<div>
											<input id="fileInput_c_0" type="file" name="userfile_c[]" />
											<label for="fileInput_c_0">+</label>      
										</div>
									</div>
								</div>

									<script type="text/javascript" >
									var fileInput = document.getElementById('fileInput_c_0');
									var filesList =  document.getElementById('fileList_c');  
									var idBase = "fileInput_c_";
									var idCount = 0;
									
									var inputFileOnChange = function() {
									
										var existingLabel = this.parentNode.getElementsByTagName("LABEL")[0];
										var isLastInput = existingLabel.childNodes.length<=1;
									
										if(!this.files[0]) {
											if(!isLastInput) {
												this.parentNode.parentNode.removeChild(this.parentNode);
											}
											return;
										}
									
										var filename = this.files[0].name;
										
									
										var deleteButton = document.createElement('span');
										deleteButton.innerHTML = '&times;';
										deleteButton.onclick = function(e) {
											this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);
										}
										var filenameCont = document.createElement('span');
										filenameCont.innerHTML = filename;
										existingLabel.innerHTML = "";
										existingLabel.appendChild(filenameCont);
										existingLabel.appendChild(deleteButton);
										
										if(isLastInput) {	
											var newFileInput=document.createElement('input');
											newFileInput.type="file";
											newFileInput.name="userfile_c[]";
											newFileInput.id=idBase + (++idCount);
											newFileInput.onchange=inputFileOnChange;
											var newLabel=document.createElement('label');
											newLabel.htmlFor = newFileInput.id;
											newLabel.innerHTML = '+';
											var newDiv=document.createElement('div');
											newDiv.appendChild(newFileInput);
											newDiv.appendChild(newLabel);
											filesList.appendChild(newDiv);
										} 
									}
									
									fileInput.onchange=inputFileOnChange;
									</script>
									
									
									
									
									
						
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
						        <h4 class="modal-title">Comunicazioni al responsabile incarico</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off"  enctype="multipart/form-data" action="incarichi/comunicazione_a_UO.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['id_uo'];?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $id_lavorazione;?>" />
									<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
										 <div class="form-group">
									    <label for="note">Testo comunicazione</label>
									    <textarea required="" class="form-control" id="note"  name="note" rows="3"></textarea>
									  </div>
									  
									<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->
									<!--div class="form-group">
									   <label for="note">Eventuale allegato</label>
										<input type="file" class="form-control-file" name="userfile[]" id="userfile" multiple>
									</div-->
									<style type="text/css">
									#fileList_i > div > label > span:last-child {
										color: red;
										display: inline-block;
										margin-left: 7px;
										cursor: pointer;
									}
									#fileList_i input[type=file] {
										display: none;
									}
									#fileList_i > div:last-child > label {
										display: inline-block;
										width: 23px;
										height: 23px;
										font: 16px/22px Tahoma;
										color: orange;
										text-align: center;
										border: 2px solid orange;
										border-radius: 50%;
									}
									</style>

								<div class="form-group file">
								   <label for="note2">Eventuali allegati</label>
								   <div id="fileList_i">
										<div>
											<input id="fileInput_i_0" type="file" name="userfile_i[]" />
											<label for="fileInput_i_0">+</label>      
										</div>
									</div>
								</div>

									<script type="text/javascript" >
									var fileInput2 = document.getElementById('fileInput_i_0');
									var filesList2 =  document.getElementById('fileList_i');  
									var idBase2 = "fileInput_i_";
									var idCount2 = 0;
									
									var inputFileOnChange2 = function() {
									
										var existingLabel2 = this.parentNode.getElementsByTagName("LABEL")[0];
										var isLastInput2 = existingLabel2.childNodes.length<=1;
									
										if(!this.files[0]) {
											if(!isLastInput2) {
												this.parentNode.parentNode.removeChild(this.parentNode);
											}
											return;
										}
									
										var filename2 = this.files[0].name;
									
										var deleteButton2 = document.createElement('span');
										deleteButton2.innerHTML = '&times;';
										deleteButton2.onclick = function(e) {
											this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode);
										}
										var filenameCont2 = document.createElement('span');
										filenameCont2.innerHTML = filename2;
										existingLabel2.innerHTML = "";
										existingLabel2.appendChild(filenameCont2);
										existingLabel2.appendChild(deleteButton2);
										
										if(isLastInput2) {	
											var newFileInput2=document.createElement('input');
											newFileInput2.type="file";
											newFileInput2.name="userfile_i[]";
											newFileInput2.id=idBase2 + (++idCount2);
											newFileInput2.onchange=inputFileOnChange2;
											var newLabel2=document.createElement('label');
											newLabel2.htmlFor = newFileInput2.id;
											newLabel2.innerHTML = '+';
											var newDiv2=document.createElement('div');
											newDiv2.appendChild(newFileInput2);
											newDiv2.appendChild(newLabel2);
											filesList2.appendChild(newDiv2);
										} 
									}
									
									fileInput2.onchange=inputFileOnChange2;
									</script>
						
						
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
					} #chiudo il while di $r
					if ($check_segnalazione==1){
					?>
					<h3><i class="fas fa-list-ul"></i> Segnalazioni collegate all'incarico </h3><br>

					<?php
					
					// fine $query che verifica lo stato
					$query= "SELECT * FROM segnalazioni.".$table." WHERE id=".$id." and id_stato_incarico =".$stato_attuale."  ORDER BY id_segnalazione;";
					
					
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
						$no_segn=1; //non sono nella pagina della segnalazione--> disegno marker
						$zoom=16;
						}
						} else {
							// fine $query che verifica lo stato
							$query= "SELECT * FROM segnalazioni.".$table." WHERE id=".$id." and id_stato_incarico =".$stato_attuale."  ORDER BY id_pc;";      
							$result=pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {
								?>
								<a class="btn btn-info" href="dettagli_provvedimento_cautelare.php?id=<?php echo $r["id_pc"];?>"><i class="fas fa-undo"></i> Torna al PC <?php echo $r["id_pc"];?></a>
								<?php
							}
							
							$zoom=16;
						}
						?>
						
						<br>
						
						<br>
						</div> 
						<div class="col-md-6">
						<h4> <i class="fas fa-map-marker-alt"></i> Indirizzo </h4>
						
						<?php
						require('./indirizzo_embedded.php');
						//echo $lon;
						$zoom=16;
						?>
						<hr>
						<h4>  <div id="break"> <i class="fas fa-map-marked-alt"></i> Mappa </div> </h4>
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



<!-- Modal mail-->
<div id="mail" class="modal fade" role="dialog">
	<div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
		<div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Invio mail</h4>
		</div>
		<div class="modal-body">
		<ul>
		<li>E' possibile inviare una mail a un indirizzo per volta.</li>
		<li>Le mail verranno inviate in automatico anche alle caselle mail di 
		<?php echo $uo_desc;?> registrate a sistema.</li>
		</ul>
		<form autocomplete="off"  enctype="multipart/form-data"  action="incarichi/mail_squadra.php?id=<?php echo $id; ?>" method="POST">
			<input type="hidden" name="uo" value="<?php echo $uo_desc;?>" />
			<input type="hidden" name="id_uo" value="<?php echo $id_uo_mail;?>" />
			<input type="hidden" name="lat" value="<?php echo $lat;?>" />
			<input type="hidden" name="lon" value="<?php echo $lon;?>" />
			<input type="hidden" name="indirizzo_stampa" value="<?php echo $indirizzo_stampa;?>" />
			<?php
			
			?>
			<div class="form-group">
				<label for="address">Specifica un indirizzo e-mail</label>  <font color="red">*</font>
				<input type="email" required="" class="form-control" id="address"  name="address" rows="3"></input>
			</div>
			
			<div class="form-group">
				<label for="address">Descrizione dell'incarico</label>  <font color="red">*</font>
				<input type="text" required="" readonly="" class="form-control" id="descrizione"  name="descrizione" 
				value="<?php echo $descrizione_incarico; ?>"></input>
			</div>
			
			<div class="form-group">
				<label for="note">Eventuali note aggiuntive comunicazione</label>  <font color="red">*</font>
				<textarea  class="form-control" id="note"  name="note" rows="3"></textarea>
				</div>
			
			<div class="form-group">
				<label for="note">Eventuale allegato</label>
				<input type="file" class="form-control-file" name="userfile" id="userfile">
			</div>
			
			<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->
			<!--div class="form-group">
				<label for="note">Eventuale allegato</label>
				<input type="file" class="form-control-file" name="userfile" id="userfile">
			</div-->

		<button  id="conferma" type="submit" class="btn btn-primary">Invia mail</button>
			</form>

		</div>
		<div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
		</div>
	</div>

	</div>
</div>


<?php 

require('./req_bottom.php');

include './mappa_leaflet_embedded.php';





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
