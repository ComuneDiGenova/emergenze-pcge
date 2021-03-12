<?php 
// Start the session
session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;
//$_SESSION['user']="MRZRRT84B01D969U";

$id=$_GET["id"];
$id_provvedimento=$id;
$subtitle="Dettagli Provvedimento Cautelare n. ".$id;


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
		FROM segnalazioni.t_provvedimenti_cautelari s
		JOIN eventi.t_eventi e on e.id=s.id_evento
		WHERE s.id=".$id.";";

$result_e=pg_query($conn, $query_evento_aperto);
while($r_e = pg_fetch_assoc($result_e)) {
	if($r_e['valido']=='f') {
		$table='v_provvedimenti_cautelari_eventi_chiusi';
		//echo "false";
	} else {
		$table='v_provvedimenti_cautelari';
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
				$query= "SELECT *, st_x(geom_inizio) as lon , st_y(geom_inizio) as lat FROM segnalazioni.".$table." WHERE id=".$id." ORDER BY data_ora_stato DESC LIMIT 1;";
				//echo $query;
				$check_punto=0;
				$result=pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					
					//echo $profilo_sistema;
				   //operatore 
				   // 0 : no permessi 
				   // 1 : permessi centrale PC
				   // 2 : permessi squadra
				   $id_squadra_attiva=$r['id_squadra'];
				   //$check_operatore=0;
					$id_squadra=$r['id_squadra'];
					$id_uo=$r['id_squadra'];
					$id_profilo=$r['id_profilo'];
					//echo $id_squadra_attiva;
					require('./check_operatore.php');
					//echo $check_uo;
					
               if ($r['data_ora_invio']>='2019-11-26'){
				?>
				<h4><b>Provvedimento ricevuto da</b>: <?php echo $r['descrizione_uo'];?>
				<?php
			   } else {
				?>
               <h4><b>Provvedimento associato a</b>: <?php echo $r['descrizione_uo'];?>
               <?php
			   }
               if ($check_uo==1){
						echo ' ( <i class="fas fa-user-check" style="color:#5fba7d"></i> )';
				}
				require('./check_responsabile.php');
				?>
               </h4>
               <h4><br><b>Tipo provvedimento</b>: <?php echo $r['tipo_provvedimento']; ?></h4>
			   
			   <?php 
			   //echo $r['id_civico_inizio'];
			   if ($r['id_civico_inizio']!='') {
				   $query_civico="SELECT * FROM geodb.civici WHERE id=".$r['id_civico_inizio'].";"; 
				   $result_c=pg_query($conn, $query_civico);
					while($r_c = pg_fetch_assoc($result_c)) {
						echo "<h4><br><b>Tratto strada</b>: ",$r_c['desvia'].", da civico ".$r_c['testo']." ";
					}
				    $query_civico="SELECT * FROM geodb.civici WHERE id=".$r['id_civico_fine'].";"; 
				   $result_c=pg_query($conn, $query_civico);
					while($r_c = pg_fetch_assoc($result_c)) {
						echo " a civico ".$r_c['testo']." ";
					}
					//echo $query_civico;
			   } else if ($r['desc_via']){
				   $query_via="SELECT * FROM geodb.v_vie_unite WHERE codvia='".$r['codvia']."';"; 
				   //echo $query_via;
				   $result_v=pg_query($conn, $query_via);
					while($r_v = pg_fetch_assoc($result_v)) {
						echo "<h4><br><b>Tratto strada</b>: ".$r_v['desvia']." - ",$r['desc_via']."</h4>";
					}
				   
				   
			   }
			   
			   
			   
			   //anagrafe civico
			   if ($r['tipo_oggetto']=='geodb.civici'){
				
				$query_or3="SELECT * FROM geodb.civici WHERE id = ".$r['id_oggetto'].";";
				$result_or3=pg_query($conn, $query_or3);
				while($r_or3 = pg_fetch_assoc($result_or3)) {
					$numciv=ltrim($r_or3["numero"],'0');
					$via=$r_or3["desvia"];
					$lettera=$r_or3["lettera"];	
				}
				//echo "<br><b>Indirizzo</b>: ".$via. ", " .$numciv;
				?>
				<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#anagrafe_civico"><i class="fas fa-address-book"></i> Visualizza anagrafe civico </button>
				<!-- Modal incarico-->
						<div id="anagrafe_civico" class="modal fade" role="dialog">
						  <div class="modal-dialog modal-lg">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Anagrafe civico di <?php echo $via. " Numero: " .$numciv. " " .$lettera ;?></h4>
							  </div>
							  <div class="modal-body">
							  

								<div id="toolbar">
				            <select class="form-control">
				                <option value="">Esporta i dati visualizzati</option>
				                <option value="all">Esporta tutto (lento)</option>
				                <option value="selected">Esporta solo selezionati</option>
				            </select>
				        </div>
				        
				        <table  id="t_anagrafe_civico" class="table-hover" style="word-break:break-all; word-wrap:break-word; " data-toggle="table" data-url="./tables/griglia_anagrafe_civico.php?id=<?php echo $r['id_oggetto'];?>" data-height="900"  data-show-export="true" data-search="true" data-click-to-select="true" data-pagination="false" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
				        
				        
						<thead>
				
				 			<tr>
			            <th data-field="state" data-checkbox="true"></th>
			             <th data-field="codfisc" data-sortable="false" data-visible="true">CF</th>
			            <th data-field="matricola" data-sortable="false" data-visible="true">Matricola</th>
			            <th data-field="famiglia" data-sortable="false"  data-visible="true">Fam</th>
			            <th data-field="cognome" data-sortable="false"  data-visible="true">Cognome</th>
			            <th data-field="nome" data-sortable="false"  data-visible="true">Nome</th>
			            <th data-field="anni" data-sortable="false"  data-visible="true">Anni</th>
			            <th data-field="sesso" data-sortable="false"  data-visible="true">M/F</th>
			            <th data-field="numint" data-sortable="false"  data-visible="true">Interno</th>
			            <th data-field="scala" data-sortable="false"  data-visible="true">Scala</th>
				    	</tr>
						</thead>
				
				</table>
				
				
				<script>
				    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
				    var $table = $('#t_anagrafe_civico');
				    $(function () {
				        $('#toolbar').find('select').change(function () {
				            $table.bootstrapTable('destroy').bootstrapTable({
				                exportDataType: $(this).val()
				            });
				        });
				    })
				</script>

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
			   
               <h4><br><b>Descrizione Provvedimento Cautelare</b>: <?php echo $r['descrizione']; ?></h4>
               <h4><br><b>Data e ora invio provvedimento</b>: <?php echo $r['data_ora_invio']; ?></h4>
               
               <hr>
            	
						
						<?php 
						$lon=$r['lon'];
						$lat=$r['lat'];
						$id_civico=$r['id_civico'];
						$geom=$r['geom_inizio'];
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
						
						$stato_attuale=$r["id_stato_provvedimenti_cautelari"];
						if ($r["id_stato_provvedimenti_cautelari"]==1){
							echo '<i class="fas fa-pause" style="color:orange"></i> ';
						} else if  ($r["id_stato_provvedimenti_cautelari"]==2) {
							if ($r['time_start']!=null) {
								echo '<i class="fas fa-play" style="color:green"></i> ';
							} else {
								echo '<i class="fas fa-play" style="color:orange"></i> ';
							}
						} else if  ($r["id_stato_provvedimenti_cautelari"]==3) {
							echo '<i class="fas fa-check"></i> ';
						} else if  ($r["id_stato_provvedimenti_cautelari"]==4) {
							echo '<i class="fas fa-times-circle"></i> ';
						}
						
						
						
						echo $r['descrizione_stato'];
						if ($r["rimosso"]=='t') {
							echo " (rimosso)";
						}
						echo '</h2>';
						echo '<hr>';
						echo '<h4>Incarichi associati al provvedimento</h4>';
						$query_i= "SELECT * FROM segnalazioni.join_incarico_provvedimenti_cautelari where id_provvedimento=".$id.";"; 
						$result_i=pg_query($conn, $query_i);
						echo '<lu>';
						while($r_i = pg_fetch_assoc($result_i)) {
							$query_ii= 'SELECT descrizione, descrizione_stato FROM segnalazioni.v_incarichi_last_update 
							WHERE id = '.$r_i['id_incarico'].';';
							$result_ii=pg_query($conn, $query_ii);
							while($r_ii = pg_fetch_assoc($result_ii)) {
								echo '<li>'.$r_ii['descrizione'] . ' (' . $r_ii['descrizione_stato'] .') - ';
							}
							echo '<a class="btn btn-info" href="dettagli_incarico.php?id='.$r_i['id_incarico'].'" > 
							Dettagli incarico '.$r_i['id_incarico'].'</a></li><br>';
						}
						echo '</lu>';
								?>
								<button type="button" class="btn btn-info"  
								data-toggle="modal" data-target="#new_incarico"><i class="fas fa-plus"></i> 
								Assegna incarico </button>
								<?php
						
						if ($r["rimosso"]=='t' and $stato_attuale==3){
							echo '<br><br><i class="fas fa-times"></i> Provvedimento rimosso con 
							successiva ordinanza sindacale alle ore ';
							$query_ore="SELECT to_char(data_ora_stato, 'HH24:MI'::text) as ora,
							to_char(data_ora_stato, 'DD/MM/YYYY'::text) as data 
  							FROM segnalazioni.t_ora_rimozione_provvedimenti_cautelari where id_provvedimento=".$id.";";
  							//echo $query_ore;
							$result_ore=pg_query($conn, $query_ore);
							while($r_ore = pg_fetch_assoc($result_ore)) {
							  echo $r_ore['ora'].' del '.$r_ore['data'];
							}
							echo "</h2><hr>";
						} else { //if ($stato_attuale==3) {
							echo "</h2><hr>";
							$query_segn='SELECT in_lavorazione 
							from segnalazioni.v_segnalazioni
							WHERE id ='.$r['id_segnalazione'].' and in_lavorazione=\'f\';';
							$check_lav_s=1;
							$result_segn=pg_query($conn, $query_segn);
							while($r_segn = pg_fetch_assoc($result_segn)) {
								$check_lav_s=0;
							}
							 
							if ($r['id_segnalazione']!='' AND $check_lav_s==1 ){
								echo'<h5> Per rimuovere il provvedimento torna alla 
								segnalazione e segui le istruzioni';
							
								// fine $query che verifica lo stato
								$query_s= "SELECT * FROM segnalazioni.".$table." WHERE id=".$id." and id_stato_provvedimenti_cautelari =".$stato_attuale."  ORDER BY id_segnalazione;";
								//echo $query
								$result_s=pg_query($conn, $query_s);
								while($r_s = pg_fetch_assoc($result_s)) {
									//echo '<b>Unità operativa</b>: '.$r['descrizione_uo'];
									?>
									<a class="btn btn-info" href="dettagli_segnalazione.php?id=<?php echo $r_s["id_segnalazione"];?>">
									<i class="fas fa-undo"></i> Torna alla segnalazione <?php echo $r_s["id_segnalazione"];?></a>
									</h5>
									<?php
								}
							
							} else {
								echo'<h5> Se la situazione fosse tornata normale, <b>in presenza di una nuova ordinanza sindacale</b>, 
								è possibile rimuovere il provvedimento cautelare. <br><br>';
								echo 'Prima di tutto è necessario  assegnare uno o più incarichi per ripristinare la situazione.';
								if ($r[id_tipo_pc]==1) {
									echo '(far rientrare i residenti)';
								} else if ($r[id_tipo_pc]==2) {
									echo '(riaprire il sottopasso)';
								} else if($r[id_tipo_pc]==3) {
									echo '(riaprire al transito la strada)';
								} 
								echo '<br><br>Una volta completati gli incarichi è possibile rimuovere il Provvedimento dal sistema</h5>';
								
								
								?>
								<button type="button" class="btn btn-info"  
								data-toggle="modal" data-target="#new_incarico"><i class="fas fa-plus"></i> 
								Assegna incarico </button>
								- 
								<button type="button" class="btn btn-danger"  
								data-toggle="modal" data-target="#rimuovi_pc"><i class="fas fa-times"></i> 
								Rimuovi provvedimento cautelare </button>
						 			
								
								
								
								
								
								
								
						<div id="rimuovi_pc" class="modal fade" role="dialog">
						  <div class="modal-dialog modal-lg">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Conferma</h4>
							  </div>
							  <div class="modal-body">
								Confermi di voler rimuovere il presente Provvedimento Cautelare? <br><br> Ricorda che per 
								rimuovere un provvedimento cautelare è necessaria una nuova ordinanza sindacale.
								<br><br>Se sei in possesso di ordinanza sindacale prima di tutto assegna uno 
								o più incarichi per ripristinare la situazione. 
								
								Solo una volta completati gli incarichi rimuovi il PC dal sistema.
								<hr>
								<form autocomplete="off" action="provvedimenti_cautelari/rimuovi.php?id=<?php echo $id; ?>" method="POST">
									<button  id="conferma" type="submit" class="btn btn-warning">Gli incarichi sono stati completati?
									<br>Rimuovi il provvedimento cautelare</button>
									
									<button type="button" class="btn btn-default" data-dismiss="modal">Gli incarichi non sono stati completati?</button>
								</form>
	
							  </div>
							
							</div>

						  </div>
						</div>
						
						
								<hr>
								
								<?php
								//fine modal
								
								
								
								
								
								
								
							
							}

						}
						
						
						if ($stato_attuale==1){
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
								echo '<a class="btn btn-info" href="provvedimenti_cautelari/sollecito.php?id='.$id.'&u='.$r['id_squadra'].'"> <i class="fas fa-at"></i> Invia sollecito </a> ';
							
							}
							
							
							
							if ($check_uo==1 or $check_operatore==1){
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
						        <h4 class="modal-title">Prendi in carico</h4>
						      </div>
						      <div class="modal-body">
						      
						
						   <form autocomplete="off" action="provvedimenti_cautelari/accetta.php?id=<?php echo $id; ?>" method="POST">
							<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
							<input type="hidden" name="squadra" value="<?php echo $r['id_squadra'];?>" />
							<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
							<!--input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" /-->
								
									 <div class="form-group">
						<label for="data_inizio">Data prevista per eseguire il provvedimento cautelare (AAAA-MM-GG) </label>  <font color="red">*</font>                 
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
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Prendi in carico</button>
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
						        <h4 class="modal-title">Rifiuta PC</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off" action="provvedimenti_cautelari/rifiuta.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
									<input type="hidden" name="squadra" value="<?php echo $r['id_squadra'];?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
										 <div class="form-group">
									    <label for="note_rifiuto">Note rifiuto</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note_rifiuto"  name="note_rifiuto" rows="3"></textarea>
									  </div>
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Rifiuta il pc</button>
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
						} else if ($stato_attuale==2) {
						?>
							<hr><!--h4><br><b>Ora prevista per eseguire il provvedimento</b>: <?php echo $r['time_preview']; ?></h4-->
							<?php if ($r['time_start']==''){
								if ($check_uo==1 or $check_operatore==1){
								?>
								<a class="btn btn-success" title="Il personale è sul posto" href="./provvedimenti_cautelari/start.php?id=<?php echo $id;?>"><i class="fas fa-play"></i> In lavorazione </a><br><br>
							<?php 
								}
							} else { ?>
								<h4><br><b>Ora inizio elaborazione provvedimento</b>: <?php echo $r['time_start']; ?></h4> 
							<?php } 
							 	if ($check_uo==1 or $check_operatore==1){
							?>
							<button type="button" class="btn btn-danger"  data-toggle="modal" data-target="#chiudi"><i class="fas fa-stop"></i> Provvedimento completato</button>
						<?php	
							}
						} else if ($stato_attuale==3) {
						?>
							<h4><br><b>Ora prevista per eseguire il provvedimento</b>: <?php echo $r['time_preview']; ?></h4>
							<h4><br><b>Ora inizio esecuzione del provvedimento</b>: 
							<?php 
							if($r['time_start']!=''){
								echo $r['time_start']; 
							} else {
								echo 'n.d (non in corso o avvio non inserito a sistema)';
							}
							?>
							</h4>
							<h4><br><b>Ora chiusura provvedimento cautelare</b>: <?php echo $r['time_stop']; ?></h4><hr>
							<h4><br><b>Note chiusura provvedimento cautelare</b>: <?php echo $r['note_ente']; ?></h4><hr>
						
						<?php	
						} else if ($r["id_stato_provvedimenti_cautelari"]==4) {
						?>	
							<h4><br><b>Note rifiuto provvedimento cautelare</b>: <?php echo $r['note_rifiuto']; ?></h4><hr>
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
						        <h4 class="modal-title">Provvedimento completato</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off" action="provvedimenti_cautelari/chiudi.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
									<input type="hidden" name="squadra" value="<?php echo $r['id_squadra'];?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
										 <div class="form-group">
									    <label for="note_rifiuto">Note chiusura</label>  <font color="red">*</font>
									    <textarea required="" class="form-control" id="note_rifiuto"  name="note_rifiuto" rows="3"></textarea>
									  </div>
						
						
						
						        <button  id="conferma" type="submit" class="btn btn-primary">Provvedimento completato</button>
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
					//}
					if ($lat==''){
						$check_punto=1;
						$query= "SELECT st_x(st_centroid(geom)) as lon , st_y(st_centroid(geom)) as lat FROM segnalazioni.".$table." WHERE id=".$id." ORDER BY data_ora_stato DESC LIMIT 1;";
						//echo $query;
						$result=pg_query($conn, $query);
						while($r = pg_fetch_assoc($result)) {
							$lon=$r['lon'];
							$lat=$r['lat'];
						}
					}
						
					
					echo "<hr>";
					if ($check_segnalazione==1){
						include 'incarichi/panel_comunicazioni.php';
					} else{
						include 'provvedimenti_cautelari/panel_comunicazioni.php';
					}
					
					if ($stato_attuale<3){ 
					?>
					<div style="text-align: center;">
					<?php 
					if ($check_uo==1 or $check_operatore==1 ){
					?>
					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_da_UO"><i class="fas fa-comment"></i> Inserisci comunicazione ricevuta</button>
					<?php }
					if ($check_operatore==1){ ?>
					<!--button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_a_UO"><i class="fas fa-comment"></i> Invia comunicazione a Squadra</button-->
					<?php } ?>
					</div>
					
					<!-- Modal comunicazione da UO-->
						<div id="comunicazione_da_UO" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Comunicazione a responsabile provvedimento</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off"  enctype="multipart/form-data"  action="provvedimenti_cautelari/comunicazione_da_UO.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['descrizione_uo'];?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
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
					
					
					<!-- Modal comunicazione a UO (non usata)-->
						<div id="comunicazione_a_UO"  class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Comunicazione a squadra responsabile provvedimento</h4>
						      </div>
						      <div class="modal-body">
						      
						
						        <form autocomplete="off"  enctype="multipart/form-data" action="provvedimenti_cautelari/comunicazione_a_UO.php?id=<?php echo $id; ?>" method="POST">
									<input type="hidden" name="uo" value="<?php echo $r['id_squadra'];?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
									<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
										 <div class="form-group">
									    <label for="note">Testo comunicazione</label>
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
					
					
					
					<?php
					} //fine if su $stato_attuale
					
					if ($check_segnalazione==1){
					?>
					
					
					
					
					
					<hr>
					<h3><i class="fas fa-list-ul"></i> Segnalazioni collegate al provvedimento </h3><br>

					<?php
					
					
					// fine $query che verifica lo stato
					$query= "SELECT * FROM segnalazioni.".$table." WHERE id=".$id." and id_stato_provvedimenti_cautelari =".$stato_attuale."  ORDER BY id_segnalazione;";
					
					
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
										</div>
										<?php
										$id_segnalazione=$r['id_segnalazione'];
										//include './segnalazioni/section_oggetto_rischio.php';
										?>
										
							
							
										
									
									
												<!--/div-->
									    </div>
									  </div>
									</div>
						
									<a class="btn btn-info" href="dettagli_segnalazione.php?id=<?php echo $r["id_segnalazione"];?>"><i class="fas fa-undo"></i> Torna alla segnalazione <?php echo $r["id_segnalazione"];?></a>
						<br><br>
						<?php
					}
					}
					$no_segn=1; //non sono nella pagina della segnalazione--> disegno marker
					$zoom=16;
					?>
						<!-- Modal incarico-->
						<div id="new_incarico" class="modal fade" role="dialog">
						  <div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Nuovo incarico</h4>
							  </div>
							  <div class="modal-body">
							  

								<form autocomplete="off" action="incarichi/nuovo_incarico.php?id_pc=<?php echo $id; ?>" method="POST">
								<input type="hidden" name="id_profilo" id="hiddenField" value="<?php echo $profilo_sistema ?>" />
								
								<?php
								if($id_profilo==5 or $id_profilo==6) {
									if($id_profilo==5){
										$query = "select concat('com_',cod) as cod, descrizione from varie.t_incarichi_comune";
										$query = $query ." where (cod like '%MU%' and descrizione not like '% ".integerToRoman($id_municipio)."') or (cod not like '%MU%' and descrizione ilike 'distretto ".$id_municipio."')";
										$query = $query ." order by descrizione;";
										//echo $query;
									} else {
										$query = "select concat('com_',cod) as cod, descrizione from varie.t_incarichi_comune";
										$query = $query ." where (cod not like '%MU%' and descrizione not like '%".$id_municipio."%') or (cod like '%MU%' and descrizione like '% ".integerToRoman($id_municipio)."%')";
										$query = $query ." order by descrizione;";
									}
								//$result = pg_query($conn, $query);

								?>
								<div class="form-group">
									  <label for="id_civico">Seleziona l'Unità Operativa cui assegnare l'incarico:</label> <font color="red">*</font>
										<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
										<option value=""> ...</option>
										<?php
										$resultr = pg_query($conn, $query);
										while($rr = pg_fetch_assoc($resultr)) {
										?>	
										<option name="id_uo" value="<?php echo $rr['cod'];?>" ><?php echo $rr['descrizione'];?></option>
										<?php } ?>
									</select>         
									 </div>
								<?php
								
									} else {
									
									?>
								<div class="form-group">
								 <label for="tipo">Tipologia di incarico:</label> <font color="red">*</font>
									<select class="form-control" name="tipo" id="tipo" onChange="getUO(this.value);"  required="">
									   <option name="tipo" value="" >  </option>
									<option name="tipo" value="direzioni" > Incarico a Direzioni (COC) </option>
									<option name="tipo" value="municipi" > Incarico a municipi </option>
									<option name="tipo" value="distretti" > Incarico a distretti di PM </option>
									<option name="tipo" value="esterni" > Incarico a Unità Operative esterne. </option>
								</select>
								</div>
									 
												 <script>
									function getUO(val) {
										$.ajax({
										type: "POST",
										url: "get_uo.php",
										data:'cod='+val,
										success: function(data){
											$("#uo-list").html(data);
										}
										});
									}

									</script>

									 
									 
									<div class="form-group">
									  <label for="id_civico">Seleziona l'Unità Operativa cui assegnare l'incarico:</label> <font color="red">*</font>
										<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
										<option value=""> ...</option>
									</select>         
									 </div>       
									<?php
									}
									
									?>
									<div class="form-group">
											 <label for="descrizione"> Descrizione operativa</label> <font color="red">*</font>
										<input type="text" name="descrizione" class="form-control" required="">
									   <small>Specificare in cosa consiste l'incarico da un punto di vista operativo</small>
									  </div>            
										  



								<button  id="conferma" type="submit" class="btn btn-primary">Invia incarico</button>
									</form>

							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
							  </div>
							</div>

						  </div>
						</div>

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
						<?php
						include './segnalazioni/section_oggetto_rischio.php';
						
						?>
						</div>
						
						


            </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->




<?php 



require('./req_bottom.php');
$id_segnalazione=$id;

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
