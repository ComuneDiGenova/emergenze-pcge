<?php
?>
<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
   <h3>Comunicazioni generiche</h3>
   	<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#comunicazione">
		   <i class="fas fa-plus"></i> Aggiungi comunicazione</button>
		   <ul>
				<?php
			$query='SELECT id, to_char(data_aggiornamento, \'DD/MM/YY HH24:MI\'::text) AS data_aggiornamento, testo, allegato FROM report.t_comunicazione 
			WHERE id_evento = '.$id.';';
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
					for($i=0;$i<$countfiles;$i++){
						$n_a=$i+1;
						//$testo= $testo. ' - <a href="../../'.$allegati[$i].'"> Allegato '.$n_a.'</a>';
						if(@is_array(getimagesize('../../'.$allegati[$i]))){
							//$image = true;
							$testo= $testo. '<br><img src="../../'.$allegati[$i].'" alt="'.$allegati[$i].'" width="30%"> 
							<a target="_new" title="Visualizza immagine in nuova scheda" href="../../'.$allegati[$i].'"> Apri immagine'.$n_a.'</a>';
						} else {
							//$image = false;
							$testo= $testo. '<br><a target="_new" href="../../'.$allegati[$i].'"> Apri allegato '.$n_a.' in nuova scheda</a>';
						}
					}
				}
				}
				echo $testo;
				echo "</li>";
			}
			echo "</ul><hr>";
			?>
			<!-- Modal comunicazione da UO-->
			<div id="comunicazione" class="modal fade" role="dialog">
			  <div class="modal-dialog">

				<!-- Modal content-->
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Comunicazioni sull'evento / Verbale COC</h4>
				  </div>
				  <div class="modal-body">
				  

					<form autocomplete="off"  enctype="multipart/form-data"  action="eventi/comunicazione.php?id=<?php echo $id; ?>" method="POST">
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
</div>

<?php
?>