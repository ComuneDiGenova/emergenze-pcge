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
				if ($r['allegato']!=''){
					echo " (<a href=\"../../".$r['allegato']."\">Allegato</a>)";
				}
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
						<div class="form-group">
						   <label for="note">Eventuale allegato (es. verbale COC)</label>
							<input type="file" class="form-control-file" name="userfile" id="userfile">
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
</div>

<?php
?>