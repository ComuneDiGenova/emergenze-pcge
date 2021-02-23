<?php

/*$query_comunicazioni="SELECT *";
$query_comunicazioni= $query_comunicazioni." FROM segnalazioni.v_comunicazioni 
WHERE id_lavorazione=".$id_lavorazione. ";";
*/
$query_comunicazioni="SELECT *";
if ($check_segnalazione==1){
	$query_comunicazioni= $query_comunicazioni." FROM segnalazioni.v_comunicazioni WHERE id_lavorazione=".$id_lavorazione. " 
	order by to_timestamp(data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text);";
} else {
	$query_comunicazioni= $query_comunicazioni." FROM segnalazioni.v_comunicazioni_incarichi WHERE id=".$id. " 
	order by to_timestamp(data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text);";
} 

//echo $query_comunicazioni;
$result_comunicazioni=pg_query($conn, $query_comunicazioni);
$check_messaggi_notifica=0;
$testo="";
while($r_comunicazioni = pg_fetch_assoc($result_comunicazioni)) {
	$check_messaggi_notifica=$check_messaggi_notifica+1;
	if ($check_messaggi_notifica>0){
		$testo= $testo. "<hr>";
	}
	$i=$i+1;
	$testo= $testo. "<i class=\"fa fa-comment\"></i> ". $r_comunicazioni['data_ora_stato'];
	$testo= $testo. " - Da " .$r_comunicazioni['mittente']. " a ". $r_comunicazioni['destinatario'];
	$testo= $testo. " : " .$r_comunicazioni['testo'];
	if ($r_comunicazioni['allegato']!=''){
		$allegati=explode(";",$r_comunicazioni['allegato']);
		// Count total files
		$countfiles = count($allegati);
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
}



?>


<div class="panel-group">
			  <div class="panel panel-success">
			    <div class="panel-heading">
			      <h4 class="panel-title">
			        <a data-toggle="collapse" href="#list_comunicazioni"><i class="fa fa-comments"></i>
					<?php
					if ($check_segnalazione==1){
					?>
						Visualizza tutte le comunicazioni sulla segnalazione 
						
					<?php if ($check_messaggi_notifica > 0 ){ 
			        echo "( "; 
			        ?>
			        <i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i>
			        <?php 
			        echo " ".$check_messaggi_notifica. ")"; 
			         } ?>						
						
					<?php
					} else {
					?>
						Visualizza tutte le comunicazioni sull'incarico
					
					<?php if ($check_messaggi_notifica > 0 ){ 
			        echo "( "; 
			        ?>
			        <i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i>
			        <?php 
			        echo " ".$check_messaggi_notifica. ")"; 
			         } ?>
					
					<?php
					} 
					?>
					</a>
			      </h4>
			    </div>
			    <div id="list_comunicazioni" class="panel-collapse collapse">
			      <div class="panel-body"-->
				<?php
				// cerco l'id_lavorazione
				/*$query_comunicazioni="SELECT *";
				if ($check_segnalazione==1){
					$query_comunicazioni= $query_comunicazioni." FROM segnalazioni.v_comunicazioni WHERE id_lavorazione=".$id_lavorazione. " 
					order by to_timestamp(data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text);";
				} else {
					$query_comunicazioni= $query_comunicazioni." FROM segnalazioni.v_comunicazioni_incarichi WHERE id=".$id. " 
					order by to_timestamp(data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text);";
				} 
				
				//echo $query_comunicazioni;
				//$result_comunicazioni=pg_query($conn, $query_comunicazioni);
				$i=0;
				while($r_comunicazioni = pg_fetch_assoc($result_comunicazioni)) {
					if ($i>0){
						echo "<hr>";
					}
					$i=$i+1;
					echo "<i class=\"fa fa-comment\"></i> ". $r_comunicazioni['data_ora_stato'];
					echo " - Da " .$r_comunicazioni['mittente']. " a ". $r_comunicazioni['destinatario'];
					echo " : " .$r_comunicazioni['testo'];
					if ($r_comunicazioni['allegato']!=''){
						echo '<a href="../../'.$r_comunicazioni['allegato'].'"> Allegato </a>';
					}
					//echo " - <a class=\"btn btn-info\" href=\"dettagli_incarico.php?id=".$r_comunicazioni['id']."\"> <i class=\"fas fa-info\"></i> Dettagli</a>";
				}*/
				echo $testo;
					$page = basename($_SERVER['PHP_SELF']);
					if ($page=='dettagli_segnalazione.php' and $check_lav>=0){
					?>
						<br><hr>
						<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione"><i class="fas fa-comment"></i> Invia comunicazione</button>
					<?php
					}
					?>
				
			
			
			</div>
    </div>
  </div>
</div>


<?php
//echo $profilo_sistema;
//echo 'profilo';
if ($profilo_sistema <=2){
	if ($check_segnalazione==1){
		$query_riservate="SELECT * FROM segnalazioni.t_comunicazioni_segnalazioni_riservate WHERE id_segnalazione=".$id. ";";
		//echo $query_riservate;
		$result_riservate=pg_query($conn, $query_riservate);
		$check_messaggi_riservate=0;
		$testo2="";
		while($r_riservate = pg_fetch_assoc($result_riservate)) {
			$check_messaggi_riservate=$check_messaggi_riservate+1;
			if ($check_messaggi_riservate>0){
				$testo2= $testo2. "<hr>";
			}
			$i=$i+1;
			$testo2= $testo2. "<i class=\"fa fa-comment\"></i> ". $r_riservate['data_ora_stato'];
			$testo2= $testo2. " - Da <i>" .$r_riservate['mittente'];
			$testo2= $testo2. "</i>: " .$r_riservate['testo'];
			if ($r_riservate['allegato']!=''){
				$allegati=explode(";",$r_riservate['allegato']);
				// Count total files
				$countfiles = count($allegati);
				// Looping all files
				if($countfiles > 0) {
					for($i=0;$i<$countfiles;$i++){
						$n_a=$i+1;
						//$testo2= $testo2. ' -  <a href="../../'.$allegati[$i].'"> Allegato '.$n_a.'</a>';
						if(@is_array(getimagesize('../../'.$allegati[$i]))){
							//$image = true;
							$testo2= $testo2. '<br><img src="../../'.$allegati[$i].'" alt="'.$allegati[$i].'" width="30%"> 
							<a target="_new" title="Visualizza immagine in nuova scheda" href="../../'.$allegati[$i].'"> Apri immagine'.$n_a.'</a>';
						} else {
							//$image = false;
							$testo2= $testo2. '<br><a target="_new" href="../../'.$allegati[$i].'"> Apri allegato '.$n_a.' in nuova scheda</a>';
						}
					}
				}
			}
		}
?>


<div class="panel-group">
			  <div class="panel panel-success">
			    <div class="panel-heading">
			      <h4 class="panel-title">
			        <a data-toggle="collapse" href="#list_comunicazioni_riservate"><i class="fas fa-user-secret"></i> <i class="fa fa-comments"></i>
						Visualizza le comunicazioni riservate 
					<?php if ($check_messaggi_riservate > 0 ){ 
			        echo "( "; 
			        ?>
			        <i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i>
			        <?php 
			        echo " ".$check_messaggi_riservate. ")"; 
			         } ?>						
					</a>
			      </h4>
			    </div>
			    <div id="list_comunicazioni_riservate" class="panel-collapse collapse">
			      <div class="panel-body">
				 <i class="fa fa-user-secret"></i> <i>Le comunicazioni qui presenti sono visibili solo ai profili 1 (amministratori di sistema) e  2 (responsanbile sala operativa)</i>
				<?php
				
				echo $testo2;
					$page = basename($_SERVER['PHP_SELF']);
					if ($page=='dettagli_segnalazione.php' and $check_lav>=0){
					?>
						<br><hr>
						<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#comunicazione_riservata"><i class="fas fa-comment"></i> Invia comunicazione riservata </button>
					<?php
					}
					?>
			</div>
    </div>
  </div>
</div>

<?php
	}
}
?>	









<!-- Modal comunicazione da UO-->
<div id="comunicazione" class="modal fade" role="dialog">
  <div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Comunicazioni sulla segnalazione</h4>
	  </div>
	  <div class="modal-body">
	  

		<form autocomplete="off"  enctype="multipart/form-data"  action="incarichi/comunicazione.php?id=<?php echo $id; ?>" method="POST">
			<input type="hidden" name="mittente" value="<?php echo $cognome." " .$nome. " (".$descrizione_profilo.")";?>" />
			<input type="hidden" name="id_lavorazione" value="<?php echo $r['id_lavorazione'];?>" />
			<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
				 <div class="form-group">
				<label for="note">Testo comunicazione <?php echo $id_evento;?></label>  <font color="red">*</font>
				<textarea required="" class="form-control" id="note"  name="note" rows="3"></textarea>
			  </div>
			
			<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->

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
	
<!-- Modal comunicazione da UO-->
<div id="comunicazione_riservata" class="modal fade" role="dialog">
  <div class="modal-dialog">

	<!-- Modal content-->
	<div class="modal-content">
	  <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal">&times;</button>
		<h4 class="modal-title">Comunicazioni riservate sulla segnalazione</h4>
	  </div>
	  <div class="modal-body">
	  

		<form autocomplete="off"  enctype="multipart/form-data"  action="incarichi/comunicazione_riservata.php?id=<?php echo $id; ?>" method="POST">
			<input type="hidden" name="mittente" value="<?php echo $cognome." " .$nome. " (".$descrizione_profilo.")";?>" />
			<input type="hidden" name="id_segnalazione" value="<?php echo $id;?>" />
			<input type="hidden" name="id_evento" value="<?php echo $id_evento;?>" />
				 <div class="form-group">
				<label for="note">Testo comunicazione <?php echo $id_evento;?></label>  <font color="red">*</font>
				<textarea required="" class="form-control" id="note"  name="note" rows="3"></textarea>
			  </div>
			
			<!--	RICORDA	  enctype="multipart/form-data" nella definizione del form    -->



				<style type="text/css">
				#fileList_r > div > label > span:last-child {
					color: red;
					display: inline-block;
					margin-left: 7px;
					cursor: pointer;
				}
				#fileList_r input[type=file] {
					display: none;
				}
				#fileList_r > div:last-child > label {
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
			   <div id="fileList_r">
					<div>
						<input id="fileInput_r_0" type="file" name="userfile_r[]" />
						<label for="fileInput_r_0">+</label>      
					</div>
				</div>
			</div>

				<script type="text/javascript" >
				var fileInput2 = document.getElementById('fileInput_r_0');
				var filesList2 =  document.getElementById('fileList_r');  
				var idBase2 = "fileInput_r_";
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
						newFileInput2.name="userfile_r[]";
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
				


		<button  id="conferma" type="submit" class="btn btn-primary">Invia comunicazione riservata</button>
			</form>

	  </div>
	  <div class="modal-footer">
		<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
	  </div>
	</div>

  </div>
</div>


<?php

?>