<?php 

$subtitle="Dettagli evento, allerte e F.O.C."

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

$evento_attivo=pg_escape_string($_GET['e']);

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

            <br>
                
               <?php
               if ($check_evento==1){
					$len=count($eventi_attivi);	               
				   
				   for ($k=0;$k<$len;$k++){
					   //echo $eventi_attivi[$k]."<br>";
						if ($eventi_attivi[$k]==$evento_attivo){
							$i=$k;
						}
				   }
				   // for ($i=0;$i<$len;$i++){
	               	echo '<div class="row">';
	               	echo '<div class="col-lg-5"><h2><i class="fa fa-chevron-circle-down"></i> Evento in corso  <small>(id='.$evento_attivo.')</small>';
	               	echo ' - <a href="reportistica.php?id='.$evento_attivo.'" class="btn btn-info"><i class="fa fa-file-invoice" aria-hidden="true"></i> Report 8h';
					if($profilo_sistema<=2){
						echo ' (stampa)';
					}
					echo '</a>';
					echo ' - <a href="reportistica_personale.php?id='.$evento_attivo.'" class="btn btn-info"><i class="fa fa-file-invoice" aria-hidden="true"></i> Report esteso';
					if($profilo_sistema<=2){
						echo ' (stampa)';
					}
					echo '</a></h2></div>';
	   					echo '<div class="col-lg-4"><div style="text-align: center;"><h3 id=timer'.$i.' > </h3></div></div>';
	   					?>
	   					<?php //echo $start[$i]; ?>
							<script>
							// Set the date we're counting down to
							//var countDownDate = new Date("Jan 5, 2019 15:37:25").getTime();
							var countDownDate<?php echo $i; ?> = new Date("<?php echo $start[$i]; ?>").getTime();
							
							// Update the count down every 1 second
							var x<?php echo $i; ?> = setInterval(function() {
							
							  // Get todays date and time
							  var now<?php echo $i; ?> = new Date().getTime();
							
							  // Find the distance between now and the count down date
							  var distance<?php echo $i; ?> = now<?php echo $i; ?> - countDownDate<?php echo $i; ?>;
							
							  // Time calculations for days, hours, minutes and seconds
							  var days<?php echo $i; ?> = Math.floor(distance<?php echo $i; ?> / (1000 * 60 * 60 * 24));
							  var hours<?php echo $i; ?> = Math.floor((distance<?php echo $i; ?> % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
							  if (hours<?php echo $i; ?><10) { hours<?php echo $i; ?>="0"+hours<?php echo $i; ?>}
							  var minutes<?php echo $i; ?> = Math.floor((distance<?php echo $i; ?> % (1000 * 60 * 60)) / (1000 * 60));
							  if (minutes<?php echo $i; ?><10) { minutes<?php echo $i; ?>="0"+minutes<?php echo $i; ?>}
							  var seconds<?php echo $i; ?> = Math.floor((distance<?php echo $i; ?> % (1000 * 60)) / 1000);
							  if (seconds<?php echo $i; ?><10) { seconds<?php echo $i; ?>="0"+seconds<?php echo $i; ?>}
							  
							  // Display the result in the element with id="timer"
							  document.getElementById("timer<?php echo $i; ?>").innerHTML = "<i class=\"fas fa-calendar-alt fa-fw\"></i> Giorni:"
							  +  days<?php echo $i; ?> + "   - <i class=\"fas fa-clock fa-fw\"></i> " + hours<?php echo $i; ?> + ":"
							  + minutes<?php echo $i; ?> + ":" + seconds<?php echo $i; ?> + "";
							  							
							  // If the count down is finished, write some text 
							  if (distance<?php echo $i; ?> < 0) {
							    clearInterval(x);
							    document.getElementById("timer<?php echo $i; ?>").innerHTML = "EXPIRED";
							  }
							}, 1000);
							</script>	   					
	   					<?php
						$query_v="SELECT * FROM eventi.t_attivazione_nverde WHERE id_evento=".$evento_attivo." and data_ora_fine > now();";
	   					//echo $query;
							//exit;
							$check_nverde=0;
							
							$result = pg_query($conn, $query_v);
							while($r = pg_fetch_assoc($result)) {
								$check_nverde=1;
							}
							
							
						
						
	   					$query_a="SELECT * FROM eventi.v_allerte WHERE id_evento=".$evento_attivo." and data_ora_fine_allerta > now();";
	   					//echo $query;
							//exit;
							$check_allerte=0;
							$conto=0;
							$result = pg_query($conn, $query_a);
							while($r = pg_fetch_assoc($result)) {
								$check_allerte=1;
							}
							
							$query_f="SELECT * FROM eventi.v_foc WHERE id_evento=".$evento_attivo." and data_ora_fine_foc > now();";
	   					//echo $query;
							//exit;
							$check_foc=0;
							$result = pg_query($conn, $query_f);
							while($r = pg_fetch_assoc($result)) {
								$check_foc=1;
							}
							
							
	   					
	   					echo '<div class="col-lg-3" id="sospensione'.$evento_attivo.'"><br>';
						if ($profilo_sistema <= 2){
							//sospensione
							//echo $sospensione[$i];
							//echo " - OGGI:";
							date_default_timezone_set('Europe/Rome');  // Set timezone.

							$now_time=date("Y/m/d H:i:s");
							//echo $now_time;
							$oggi = strtotime($now_time);
							$dataScadenza = strtotime($sospensione[$i]);
							//echo " - OGGI:";
							//echo $oggi;
							//echo " - DATA SCADENZA:";
							//echo $dataScadenza;
							//echo " - " ;
							if ($sospensione[$i]=='' or $dataScadenza < $oggi){
								echo '<button type="button" class="btn btn-warning" title="Sospendi evento per 8 ore. Le segnalazioni legate all\'evento sospeso non saranno visibili in mappa e nell\'elenco della prima pagina"';	
								echo 'onclick="return sospendi'.$evento_attivo.'()"><i class="fas fa-pause"></i> 8h</button> - ';
							} else {
								echo '<i class="fas fa-pause" title="Evento sospeso per 8 ore fino al '.$sospensione_c[$i].'"></i> ';
								echo '<button type="button" class="btn btn-success" title="Anticipa la riapertura dell\'evento sospeso fino al '.$sospensione[$i].'" ';	
								echo 'onclick="return riprendi'.$evento_attivo.'()"><i class="fas fa-play"></i></button> - ';
							}
							?>
							<p id="msg"></p>
							<script type="text/javascript" >
							function sospendi<?php echo $evento_attivo;?>() {
								//alert('Test1');
								//var tel=document.getElementById('telsq<?php echo $m;?>').value;
								//var dataString='tel='+tel;
								$.ajax({
									type:"post",
									url:"./eventi/sospendi.php?id=<?php echo $evento_attivo;?>",
									//data:dataString,
									cache:false,
									success: function (html) {
										//$('#msg').html(html);
										setTimeout(function(){// wait for 1 secs(2)
											location.reload(); // then reload the page.(3)
										}, 100); 
									}
								});
								//$('#navbar_emergenze').load('navbar_up.php?r=true&s=<?php echo $subtitle2;?>');
								//$('#sospensione<?php echo $evento_attivo;?>').load(document.URL +  ' #sospensione<?php echo $evento_attivo;?>');
								return false;
								
							};
							function riprendi<?php echo $evento_attivo;?>() {
								//alert('Test1');
								//var tel=document.getElementById('telsq<?php echo $m;?>').value;
								//var dataString='tel='+tel;
								$.ajax({
									type:"post",
									url:"./eventi/riprendi.php?id=<?php echo $evento_attivo;?>",
									//data:dataString,
									cache:false,
									success: function (html) {
										//$('#msg').html(html);
										setTimeout(function(){// wait for 1 secs(2)
											location.reload(); // then reload the page.(3)
										}, 100); 
									}
								});
								//$('#navbar_emergenze').load('navbar_up.php?r=true&s=<?php echo $subtitle2;?>');
								//$('#sospensione<?php echo $evento_attivo;?>').load(document.URL +  ' #sospensione<?php echo $evento_attivo;?>');
								return false;
							};
							</script>
							<?php
							//chiusura primo livello
							echo '<button type="button" class="btn btn-danger"  data-toggle="modal" ';
							if($check_allerte==1 OR $check_foc==1){
								echo 'disabled=""';
							}
							echo 'data-target="#chiudi'.$evento_attivo.'"><i class="fas fa-times"></i> Inizia fase chiusura</button>';
						}
	   					echo '</div></div>';
	   					echo '<div class="row">';
	   					echo '<div class="col-lg-6"><h3>Tipologia: '. $tipo_eventi_attivi[$i][1].'</h3>';
	   					echo '<h3>Note: '. $nota_eventi_attivi[$i][1].'</h3>';
						$qq = "SELECT * FROM verbatel.eventi_inviati WHERE evento_id=".$evento_attivo.";";
						$rr = pg_query($conn, $qq);
						$risp = 'No';
						while ($res = pg_fetch_assoc($rr)) {
							if ($res["inviato"]=='T') {
								$risp = 'Si';
							};
						}; 
				   		echo '<h3>Evento condiviso con PM: '.$risp.'</h3>';
	   					echo '</div><div class="col-lg-6"><h3>Municipi interessati: ';
	   					$len2=count($municipi);
	   					//echo $len2;	               
	               	$k=0;
	               	for ($j=0;$j<$len2;$j++){
	               		
	               		if ($municipi[$j][0]==$evento_attivo){
	               			if ($k==0) {echo $municipi[$j][1];} else {echo ', '.$municipi[$j][1];};
	               			$k=$k+1;
	               		}
	               	}
	   					//echo '</h3>Qua ci vuole la mappa dei municipi interessati';
	   					echo '</div></div><hr>';
	   					
	   				   echo '<a href="monitoraggio_meteo.php?id='.$evento_attivo.'" class="btn btn-info">
	   				   <i class="fas fa-thermometer-half"></i> Monitoraggio meteo</a>';
					   
					   ?>
					   
					   <button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_<?php echo $evento_attivo; ?>">
					   <i class="fas fa-plus"></i> Aggiungi comunicazione</button>
					   <ul>
	   					<?php
						$query='SELECT id, to_char(data_aggiornamento, \'DD/MM/YY HH24:MI\'::text) AS data_aggiornamento, testo, allegato FROM report.t_comunicazione 
						WHERE id_evento = '.$evento_attivo.';';
						//echo $query;
						$result = pg_query($conn, $query);
						$c=0;
						while($r = pg_fetch_assoc($result)) {
							if ($c==0){
								echo "<h3>Elenco comunicazioni generiche</h3>";
							}
							$c=$c+1;
							//echo '<button type="button" class="btn btn-info noprint"  data-toggle="modal" 
							//data-target="#update_mon_'.$r['id'].'">
							//<i class="fas fa-edit"></i> Edit </button>';
							echo " <li><b>Comunicazione del ".$r['data_aggiornamento']."</b>: ";
							echo $r['testo'];
							/*if ($r['allegato']!=''){
								echo " (<a href=\"../../".$r['allegato']."\">Allegato</a>)";
							}*/
							if ($r['allegato']!=''){
								$allegati=explode(";",$r['allegato']);
								// Count total files
								$countfiles = count($allegati); 
								//echo $countfiles;
								$testo='';
								// Looping all files
								if($countfiles > 0) {
									for($j=0;$j<$countfiles;$j++){
										$n_a=$j+1;
										//$testo= $testo. ' - <a href="../../'.$allegati[$i].'"> Allegato '.$n_a.'</a>';
										if(@is_array(getimagesize('../../'.$allegati[$j]))){
											//$image = true;
											$testo= $testo. '<br><img src="../../'.$allegati[$j].'" alt="'.$allegati[$j].'" width="30%"> 
											<a target="_new" title="Visualizza immagine in nuova scheda" href="../../'.$allegati[$j].'"> Apri immagine'.$n_a.'</a>';
											echo $testo;
										} else {
											//$image = false;
											$testo= $testo. '<br><a target="_new" href="../../'.$allegati[$j].'"> Apri allegato '.$n_a.' in nuova scheda</a>';
											echo $testo;
										}
									}
								}
							}
							//echo $testo;
							echo "</li>";
						}
						echo "</ul><hr>";
						?>
						<!-- Modal comunicazione da UO-->
						<div id="comunicazione_<?php echo $evento_attivo; ?>" class="modal fade" role="dialog">
						  <div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Comunicazioni sull'evento <?php echo $evento_attivo; ?> / Verbale COC dell'evento <?php echo $evento_attivo; ?></h4>
							  </div>
							  <div class="modal-body">
							  

								<form autocomplete="off"  enctype="multipart/form-data"  action="eventi/comunicazione.php?id=<?php echo $evento_attivo; ?>" method="POST">
									<input type="hidden" name="mittente" value="<?php echo $cognome." " .$nome. " (".$descrizione_profilo.")";?>" />
									<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
									<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
										 <div class="form-group">
										<label for="note">Testo comunicazione <?php echo $id_evento;?></label>  <font color="red">*</font>
										<textarea required="" class="form-control" id="note"  name="note" rows="3"></textarea>
									  </div>
									
									<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->
									<!--div class="form-group">
									   <label for="note">Eventuale allegato (es. verbale COC)</label>
										<input type="file" class="form-control-file" name="userfile" id="userfile">
									</div-->
									
									<style type="text/css">
									#fileList > div > label > span:last-child {
										color: red;
										display: inline-block;
										margin-left: 7px;
										cursor: pointer;
									}
									#fileList input[type=file] {
										display: none;
									}
									#fileList > div:last-child > label {
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
								   <div id="fileList">
										<div>
											<input id="fileInput_0" type="file" name="userfile[]" />
											<label for="fileInput_0">+</label>      
										</div>
									</div>
								</div>

									<script type="text/javascript" >
									var fileInput = document.getElementById('fileInput_0');
									var filesList =  document.getElementById('fileList');  
									var idBase = "fileInput_";
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
											newFileInput.name="userfile[]";
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
	   					<?php
	   					if ($profilo_sistema <= 2){
	   					?>
	   					<button type="button" class="btn btn-success"  data-toggle="modal" data-target="#new_nverde<?php echo $i; ?>"><i class="fas fa-plus"></i> Aggiungi attivazione n. verde</button>
	   					<?php
						}
						?>
						
						
						<?php
	   					
							if($check_nverde==1) {echo "<ul>";}
							$result = pg_query($conn, $query_v);
							while($r = pg_fetch_assoc($result)) {
							
								$timestamp = strtotime($r["data_ora_inizio"]);
								$curtime = time();
								if ($curtime-$timestamp > 0){
									$stato ='<i class="fas fa-play"></i> <b>Numero verde attivo</b>';
								} else {
									$stato = '<i class="fas fa-forward"></i> <b>Numero verde da attivare</b>';
								}
								setlocale(LC_TIME, 'it_IT.UTF8');
								$data_start = strftime('%A %e %B %G', $timestamp);
								$ora_start = date('H:i', $timestamp);
								$timestamp = strtotime($r["data_ora_fine"]);
								$data_end = strftime('%A %e %B %G', $timestamp);
								$ora_end = date('H:i', $timestamp);								
								//$color=str_replace("'","",$r["rgb_hex"]);
								echo "<li> <h5> ".$stato." dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. "</h5>";
								if ($profilo_sistema <= 2){
									echo "<a class=\"btn btn-success\"  href=\"./prolunga_nverde.php?e=".$r["id_evento"]."&t=".$r["data_ora_inizio"]."\"><i class=\"fas fa-clock\"></i> Prolunga / accorcia orari num. verde</a> - ";
									echo "<a class=\"btn btn-danger\"  href=\"eventi/remove_nverde.php?e=".$r["id_evento"]."&t=".$r["data_ora_inizio"]."\"><i class=\"fas fa-trash\"></i> Cancella attivazione numero verde </a></li>";
								}
							}							
							if($check_nverde==1) {echo "</ul>";}
							if($check_nverde==0) { echo "<h3>In questo momento il numero verde è disattivo</h3>";}

							?>
							
							<?php

							$query="SELECT * FROM eventi.t_attivazione_nverde WHERE id_evento=".$evento_attivo." and data_ora_fine <= now();";
	   					//echo $query;
							//exit;
								$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {
								$check_nverde=2;
							}
							
								
							if($check_nverde==2) {
								//echo "<h3> Allerte passate:</h3><ul>";
								?>
								<div class="panel-group">
  								<div class="panel panel-success">
								    <div class="panel-heading">
								      <h4 class="panel-title">
								        <a data-toggle="collapse" href="#collapse_nverde<?php echo $evento_attivo;?>">Attivazioni passate numero verde</a>
								      </h4>
								    </div>
								    <div id="collapse_nverde<?php echo $evento_attivo;?>" class="panel-collapse collapse">
								      <div class="panel-body"><ul>
								<?php
							}
							$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {	

								$timestamp = strtotime($r["data_ora_inizio"]);
								setlocale(LC_TIME, 'it_IT.UTF8');
								$data_start = strftime('%A %e %B %G', $timestamp);
								$ora_start = date('H:i', $timestamp);
								$timestamp = strtotime($r["data_ora_fine"]);
								$data_end = strftime('%A %e %B %G', $timestamp);
								$ora_end = date('H:i', $timestamp);								
								$color=str_replace("'","",$r["rgb_hex"]);
								//echo $color;
								echo "<li> <i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\007c37\"></i> <b>Numero verde  attivo</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " </li>";
							}
							if($check_nverde==2) {
								//echo "<h3> Allerte passate:</h3><ul>";
								?>
								 </div>
								      
								    </div>
								  </div>
								</div>
								
								
								<?php
								      
								     
							}
							?>
							<div class="row">
							<div class="col-lg-12">
	   					<?php
	   					if($check_nverde==0) {
	   						 echo " - Il numero verde non è ancora stato attivato";
	   					} else {
								echo "</ul>";
	   					}
	   					
	   					?>
						</div>
						</div>
						<div class="row">
	   					<hr>
							<div style="text-align: center;">
								<h4> Stato delle Allerte e Fasi Operative Comunali (F.O.C.) </h4><br>
							</div>
	   					
	   					<div class="col-lg-6">
	   					<div style="text-align: center;">
						<?php
						if ($profilo_sistema <= 2){
						?>	
	   					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#allertaRLG"><i class="fas fa-info"></i> Bollettino regionale (demo)</button>
	   					<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_allerta<?php echo $i; ?>"><i class="fas fa-plus"></i> Aggiungi allerta manualmente</button>
						<?php
						}
						?>
						</div>
							<hr>
							<?php
	   					
							if($check_allerte==1) {echo "<h3> Allerte in corso o previste:</h3><ul>";}
							$result = pg_query($conn, $query_a);
							while($r = pg_fetch_assoc($result)) {
								$conto=$conto+1;
								$timestamp = strtotime($r["data_ora_inizio_allerta"]);
								$curtime = time();
								if ($curtime-$timestamp > 0){$stato ='- <i class="fas fa-play"></i> <b>In corso</b>';} else { $stato = '- <i class="fas fa-forward"></i> <b>Prevista</b>';}
								setlocale(LC_TIME, 'it_IT.UTF8');
								$data_start = strftime('%A %e %B %G', $timestamp);
								$data_start_edit = strftime('%Y-%m-%d', $timestamp);
								$h_start_edit = date('H', $timestamp);
								$m_start_edit = date('i', $timestamp);
								$ora_start = date('H:i', $timestamp);
								$timestamp = strtotime($r["data_ora_fine_allerta"]);
								$data_end = strftime('%A %e %B %G', $timestamp);
								$data_end_edit = strftime('%Y-%m-%d', $timestamp);
								$h_end_edit = date('H', $timestamp);
								$m_end_edit = date('i', $timestamp);
								$ora_end = date('H:i', $timestamp);								
								$color=str_replace("'","",$r["rgb_hex"]);
								
								// ricerca bollettino
								$path_bollettini = realpath('../../bollettini/');
								$bollettino=$r["messaggio_rlg"];
								$conteggio=strlen($bollettino)*-1;
								$search="/home/local/COMGE/egter01/";
								$replace="../../";
								foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path_bollettini)) as $filename_b)
								{
									if (substr($filename_b, $conteggio)==$bollettino){
										$link_bollettino=str_replace($search, $replace, $filename_b);
									}
								
								}
								//echo $color;
								echo " <h5><i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\"\"></i> <b>Allerta ".$r["descrizione"]."</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " ";
								echo '- <a href="'.$link_bollettino.'"><i class="fas fa-file-pdf"></i> '.$bollettino.'</a> '.$stato. '</h5>';
								
								if ($profilo_sistema <= 2){
								
									echo "<a class=\"btn btn-info\"  href=\"./prolunga_allerta.php?e=".$r["id_evento"]."&a=".$r["id_tipo_allerta"]."&t=".$r["data_ora_inizio_allerta"]."\"><i class=\"fas fa-clock\"></i> Prolunga / accorcia allerta</a> - ";
									echo "<a class=\"btn btn-danger\"  href=\"eventi/remove_allerta.php?e=".$r["id_evento"]."&a=".$r["id_tipo_allerta"]."&t=".$r["data_ora_inizio_allerta"]."\"><i class=\"fas fa-trash\"></i> Cancella allerta</a></li>";
								
								}
								
							}
							if($check_allerte==1) {echo "</ul>";}
							if($check_allerte==0) { echo "<h3>Nessuna allerta in corso</h3>";}


	   					?>

		
							</div><div class="col-lg-6">
							<?php
							if ($profilo_sistema <= 2){
							?>
						   <div style="text-align: center;">
								<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_foc<?php echo $i; ?>"><i class="fas fa-plus"></i> Aggiungi F.O.C.</button>			   					
							</div>
							<?php
							}
						?>
	   					<hr>
	   					<?php
	   					
							if($check_foc==1) {echo "<h3> Fasi Operative Comunali in corso o previste:</h3><ul>";}
							$result = pg_query($conn, $query_f);
							while($r = pg_fetch_assoc($result)) {
							
								$timestamp = strtotime($r["data_ora_inizio_foc"]);
								$curtime = time();
								if ($curtime-$timestamp > 0){$stato ='- <i class="fas fa-play"></i> <b>In corso</b>';} else { $stato = '- <i class="fas fa-forward"></i> <b>Prevista</b>';}
								setlocale(LC_TIME, 'it_IT.UTF8');
								$data_start = strftime('%A %e %B %G', $timestamp);
								$ora_start = date('H:i', $timestamp);
								$timestamp = strtotime($r["data_ora_fine_foc"]);
								$data_end = strftime('%A %e %B %G', $timestamp);
								$ora_end = date('H:i', $timestamp);								
								$color=str_replace("'","",$r["rgb_hex"]);
								echo "<li> <h5><i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\"\"></i> <b>Fase di ".$r["descrizione"]."</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " ".$stato."</h5>";
								if ($profilo_sistema <= 2){
									echo "<a class=\"btn btn-info\"  href=\"./prolunga_foc.php?e=".$r["id_evento"]."&a=".$r["id_tipo_foc"]."&t=".$r["data_ora_inizio_foc"]."\"><i class=\"fas fa-clock\"></i> Prolunga / accorcia F.O.C.</a> - ";
									echo "<a class=\"btn btn-danger\"  href=\"eventi/remove_foc.php?e=".$r["id_evento"]."&a=".$r["id_tipo_foc"]."&t=".$r["data_ora_inizio_foc"]."\"><i class=\"fas fa-trash\"></i> Cancella F.O.C. </a></li>";
								}
							}							
							if($check_allerte==1) {echo "</ul>";}
							if($check_foc==0) { echo "<h3>Nessuna Fase Operativa in corso</h3>";}

							?>

							</div> <!-- col -->
							</div> <!-- row -->
							<hr>
							<div class="row">
							<div style="text-align: center;">
								<h4> Storico allerte e Fasi Operative Comunali </h4><br>
							</div>
							<div class="col-lg-6">
							<?php

							$query="SELECT * FROM eventi.v_allerte WHERE id_evento=".$evento_attivo." and data_ora_fine_allerta <= now();";
	   					//echo $query;
							//exit;
								$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {
								$check_allerte=2;
							}
							
								
							if($check_allerte==2) {
								//echo "<h3> Allerte passate:</h3><ul>";
								?>
								<div class="panel-group">
  								<div class="panel panel-primary">
								    <div class="panel-heading">
								      <h4 class="panel-title">
								        <a data-toggle="collapse" href="#collapse_allerte<?php echo $evento_attivo;?>">Allerte passate</a>
								      </h4>
								    </div>
								    <div id="collapse_allerte<?php echo $evento_attivo;?>" class="panel-collapse collapse">
								      <div class="panel-body"><ul>
								<?php
							}
							$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {	

								$timestamp = strtotime($r["data_ora_inizio_allerta"]);
								setlocale(LC_TIME, 'it_IT.UTF8');
								$data_start = strftime('%A %e %B %G', $timestamp);
								$ora_start = date('H:i', $timestamp);
								$timestamp = strtotime($r["data_ora_fine_allerta"]);
								$data_end = strftime('%A %e %B %G', $timestamp);
								$ora_end = date('H:i', $timestamp);								
								$color=str_replace("'","",$r["rgb_hex"]);
								//echo $color;
								echo "<li> <i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\"\"></i> <b>Allerta ".$r["descrizione"]."</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " </li>";
							}
							if($check_allerte==2) {
								//echo "<h3> Allerte passate:</h3><ul>";
								?>
								 </div>
								      
								    </div>
								  </div>
								</div>
								
								</div><div class="col-lg-6">
								
								<?php
								      
								     
							}
							
	   					if($check_allerte==0) { echo "<ul><li>Nessuna allerta definita</li></ul>";}
	   			
	   					
					



	   					$query="SELECT * FROM eventi.v_foc WHERE id_evento=".$evento_attivo." and data_ora_fine_foc <= now();";
	   					//echo $query;
							//exit;
							$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {
								$check_foc=2;
							}
							if($check_foc==2) {
								//echo "<h3> Fasi Operative Comunali passate:</h3><ul>";
								?>
								<div class="panel-group">
  								<div class="panel panel-primary">
								    <div class="panel-heading">
								      <h4 class="panel-title">
								        <a data-toggle="collapse" href="#collapse_foc<?php echo $evento_attivo;?>">Fasi Operative Comunali passate</a>
								      </h4>
								    </div>
								    <div id="collapse_foc<?php echo $evento_attivo;?>" class="panel-collapse collapse">
								      <div class="panel-body">
								<?php
								}
							$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {
								$timestamp = strtotime($r["data_ora_inizio_foc"]);
								setlocale(LC_TIME, 'it_IT.UTF8');
								$data_start = strftime('%A %e %B %G', $timestamp);
								$ora_start = date('H:i', $timestamp);
								$timestamp = strtotime($r["data_ora_fine_foc"]);
								$data_end = strftime('%A %e %B %G', $timestamp);
								$ora_end = date('H:i', $timestamp);
								$color=str_replace("'","",$r["rgb_hex"]);								
								echo "<li> <i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\"\"></i> <b> Fase di ".$r["descrizione"]."</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " </li>";
							}
							if($check_foc==2) {
								//echo "<h3> Allerte passate:</h3><ul>";
								?>
								 </div>
								      
								    </div>
								  </div>
								</div>
								<?php
								      
								     
							}
							
	   					if($check_foc==0) { echo "<ul><li>Nessuna Fase Operativa definita fino ad ora</li></ul>";}
	   			
	   					
					?>

					</div> <!-- Chiudo la colonna larga 6 -->
					




<!-- Modal chiusura-->
<div id="chiudi<?php echo $evento_attivo; ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Inizia fase chiusura evento</h4>
      </div>
      <div class="modal-body">
      

        <form autocomplete="off" action="eventi/chiudi_evento_0.php?id='<?php echo $evento_attivo?>'" method="POST">
		
		<?php if($check_allerte!=1 ) { ?>
		Se intendi proseguire l'evento verrà posto in fase di chiusura, non sarà più possibile definire nuove allerte, Fasi Operative Comunali
		nè creare nuove segnalazioni. Sarà comunque possibile concludere le lavorazioni sulle segnalazioni in corso.
		<hr>
		<label for="cat" class="auto-length">
			<input type="checkbox" name="cat<?php echo $i; ?>" id="cat<?php echo $i; ?>">
			Cliccare qua per confermare la chiusura dell'evento 
		</label>
		<?php } else { ?>
			Ci sono allerte in corso. Non è possibile chiudere l'evento.
		<?php } ?>
		<br><br>



        <button disabled="" id="conferma<?php echo $i; ?>" type="submit" class="btn btn-danger">Conferma inizio fase di chiusura evento</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>            			



<!-- Modal allerta RLG-->
<div id="allertaRLG" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Dettaglio allerta Regione Liguria (DEMO)</h4>
      </div>
      <div class="modal-body">
      
	  
		Questa <b>funzionalità evolutiva</b> è stata individuata sin dalla prima fase di sviluppo SW. Attualmente è solo disponibile in modalita' demo. <br> 
		Terminate le fasi di sviluppo del Nuovo Sistema di Gestione Emergenze ci si occuperà del parsing completo del bollettino 
		e della corretta impaginazione. - Gter srl
		<br><br><hr>
 			<?php

							$myfile = fopen("/opt/rh/httpd24/root/var/www/html/bollettini/allerte.txt", "r") or die("Unable to open file!");
							while ($line = fgets($myfile)) {
								// <... Do your work with the line ...>
								$check_update=0;
  								 echo($line);
							}
							fclose($myfile);
							
							
							
							
							
							
							
							
							?>
        <br><br>Da completare processo lettura e impaginazione... 

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   






<!-- Modal nverde-->
<div id="new_nverde<?php echo $i; ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Inserire attivazione n verde</h4>
      </div>
      <div class="modal-body">
      

        <form autocomplete="off" action="eventi/nuovo_nverde.php?id='<?php echo $evento_attivo?>'" method="POST">
		
		
			
            
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio attivazione (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date10_<?php echo $i; ?>" required>
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
						<label for="data_fine" >Data fine attivazione (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_fine" id="js-date11_<?php echo $i; ?>" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Definisci attivazione n.verde</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   





<!-- Modal allerta-->
<div id="new_allerta<?php echo $i; ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Inserire allerta</h4>
      </div>
      <div class="modal-body">
      

        <form autocomplete="off" action="eventi/nuova_allerta.php?id='<?php echo $evento_attivo?>'" method="POST">
		
		
			<div class="form-group">
              <label for="tipo">Bollettino:</label> <font color="red">*</font>
                            <select class="form-control" name="bollettino" id="bollettino" required="">
                            <!--option name="tipo" value="" > ... </option-->
            <?php            
            $query2="SELECT * From eventi.v_bollettini WHERE tipo ilike 'Bollettino allerte' ;";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>    
                    <option name="bollettino" value="<?php echo $r2['nomefile'];?>" ><?php echo $r2['nomefile'].' ('.$r2['data_download'].")";?></option>
             <?php } ?>

             </select>            
             </div>
			
		   <div class="form-group">
              <label for="tipo">Tipo allerta:</label> <font color="red">*</font>
                            <select class="form-control" name="tipo" id="tipo" required="">
                            <option name="tipo" value="" > ... </option>
            <?php            
            $query2="SELECT * From \"eventi\".\"tipo_allerta\" WHERE valido='t' order by id;";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>    
                    <option name="tipo" value="<?php echo $r2['id'];?>" ><?php echo $r2['descrizione'];?></option>
             <?php } ?>

             </select>            
             </div>
            
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio allerta (AAAA-MM-GG) </label> <font color="red">*</font>                
						<input type="text" class="form-control" name="data_inizio" id="js-date<?php echo $i; ?>" required>
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
						<label for="data_fine" >Data fine allerta (AAAA-MM-GG) </label> <font color="red">*</font>                 
						<input type="text" class="form-control" name="data_fine" id="js-date2<?php echo $i; ?>" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Crea allerta</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   




<!-- Modal foc-->
<div id="new_foc<?php echo $i; ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Inserire nuova Fase Operativa Comunale</h4>
      </div>
      <div class="modal-body">
      

        <form autocomplete="off" action="eventi/nuova_foc.php?id='<?php echo $evento_attivo?>'" method="POST">
		
		
		   <div class="form-group">
              <label for="tipo">Tipo Fase Operativa:</label> <font color="red">*</font>
                            <select class="form-control" name="tipo" id="tipo" required="">
                            <option name="tipo" value="" > ... </option>
            <?php            
            $query2="SELECT * From \"eventi\".\"tipo_foc\" WHERE valido='t' order by id;";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>    
                    <option name="tipo" value="<?php echo $r2['id'];?>" ><?php echo $r2['descrizione'];?></option>
             <?php } ?>

             </select>            
             </div>
            
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio FOC (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date3<?php echo $i; ?>" required>
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
					
					
					<div class="form-group">
						<label for="data_fine" >Data fine FOC (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_fine" id="js-date4<?php echo $i; ?>" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Crea FOC</button>
            </form>

      </div>
      
      
      
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   

<?php
echo "</div>";
echo '<hr style="border:2px solid #000;">';

?>


<script>

          
            
              




</script>


<?php


						   //}
						   


	   			} else {
						echo "<h1> Nessun evento al momento attivo. </h1>";	   				
	   				
	   				
	   				
	   				}
					?>
					

<div class="col-lg-12">
                    <!--h1 class="page-header">Titolo pagina</h1-->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <br><br>
            <div class="row">

            </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>



   
<script type="text/javascript" >

   
   (function ($) {
    'use strict';

<?php
if ($check_evento==1){
$len=count($eventi_attivi);	               
for ($i=0;$i<$len;$i++){
?>   
 
    $('[type="checkbox"][id="cat<?php echo $i; ?>"]').on('change', function () {
        if ($(this).is(':checked')) {
            $('#conferma<?php echo $i; ?>').removeAttr('disabled');
            return true;
        }
        
    });
    
    $('[type="checkbox"][id="cat<?php echo $i; ?>"]').on('change', function () {
        if (!$(this).is(':checked')) {
            $('#conferma<?php echo $i; ?>').attr('disabled', true);
            return true;
        }
        
    });    

    
  
$(document).ready(function() {
    $('#js-date<?php echo $i; ?>').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date2<?php echo $i; ?>').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
      $('#js-date3<?php echo $i; ?>').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date4<?php echo $i; ?>').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
    $('#js-date10_<?php echo $i; ?>').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    }); 
    $('#js-date11_<?php echo $i; ?>').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });     
});

<?php }} ?>

}(jQuery));  
     
     
$(document).ready(function() {
    
	
	
	$('#js-date9').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
	
    /*$('#js-date10').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
    
    $('#js-date11').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    }); */
});     

 </script>   

</body>

</html>
