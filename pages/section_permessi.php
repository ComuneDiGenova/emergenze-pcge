

            <div class="row">
            
            <?php
            	if ($check_profilo==1){
	            	$query="SELECT * From users.profili_utilizzatore where id=".$profilo.";";
	            	//echo $query; 
	               $result = pg_query($conn, $query);
		            while($r = pg_fetch_assoc($result)) {
		            	echo '<br><b>Profilo</b>: '. $r['descrizione'];
		            }
		            
		            $query="SELECT * From users.utenti_sistema where matricola_cf='".$id."';";
	            	//echo $query; 
	               $result = pg_query($conn, $query);
		            while($r = pg_fetch_assoc($result)) {
		            	$valido = $r['valido'];
						//echo $valido;
						$profilo_m=$r["id_profilo"].'_'.$r["cod_municipio"];
						echo ' - ' .$r["id_profilo"].'_'.$r["cod_municipio"];
		            } 
		            
		            if ($valido=='t' and $profilo_sistema==1){
		            	echo '<br> <br><a class="btn btn-warning" href="./permessi/permessi_sospendi.php?matr='.$id.'"><i class="fas fa-pause"></i> Sospendi </a>';
		            } else if ($profilo_sistema==1){
		            	echo ' (profilo sospeso)<br> <br><a class="btn btn-success" href="./permessi/permessi_riprendi.php?matr='.$id.'"><i class="fas fa-play"></i> Ri-attiva </a>';
		            }
		         } else {
		          	echo "Attualmente l'utente non ha particolari profili impostati. ";
		          	echo 'Potrà  solo accedere al form semplificato di inserimento segnalazioni da numero verde.';
		         } 
	            
	            
            ?>

            </div>
            <hr>
            </b>

<div class="row">
			<?php
			if ($profilo_sistema==1){
				//echo strlen($id);
			?>
            <form action="permessi/permessi_insert.php" method="POST">
            <!-- Devo passare al php che gestisce l'aggiornamento permessi anche la matricola con un campo nascosto-->
			<?php
			if (strlen($id)>=11){
			?>
				<input type="hidden" name="cf" id="hiddenField" value="<?php echo $id ?>" />
            <?php
			} else {
			?>
				<input type="hidden" name="matr" id="hiddenField" value="<?php echo $id ?>" />
			<?php
			}
			?>
            <div class="form-group col-lg-12">
            <label for="profilo"> Scegli il profilo </label> <font color="red">*</font><br>
            <?php
            	
            	$query="SELECT * From users.v_profili_utilizzatore order by id;";
               $result = pg_query($conn, $query);
	            while($r = pg_fetch_assoc($result)) {
	            	if($profilo==$r['id'] or $profilo_m==$r['id']){
	            		echo '<label class="radio"><input type="radio" name="profilo" checked="" value="'.$r['id'].'"> 
'.$r['id'].' - '.$r['descrizione'].'</label>';
						} else {
	            		echo '<label class="radio"><input type="radio" name="profilo" value="'.$r['id'].'"> '.$r['id'].' - 
'.$r['descrizione'].'</label>';
						}
	            }
		       
		       	if($check_profilo==0) {
	            	echo '<label class="radio"><input type="radio" name="profilo" checked="" value="no"> Nessun profilo 
</label>';
	            } else {
	            	echo '<label class="radio"><input type="radio" name="profilo" value="no"> Nessun profilo </label>';
	            }
            ?>
            </div>
            <button type="submit" class="btn btn-primary">Aggiorna permessi</button>
            </form>
            
           <?php
			} else {
				echo 'Il tuo profilo utente non &egrave abilitato alle modifiche.
				Contattare via mail l\'amministratore di sistema
				(<a href="mailto:adminemergenzepc@comune.genova.it?subject=Modifiche%20permessi%20matr%20'.$id.'">
				adminemergenzepc@comune.genova.it</a>) per modifiche ai permessi utente<hr>';
			}
			?>
            </div>
            
            <!-- /.row -->
