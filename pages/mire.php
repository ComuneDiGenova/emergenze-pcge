<?php 

$subtitle = "Monitoraggio corsi d'acqua";

// carica dipendenze
require_once './req.php';
require_once explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php';
require_once './check_evento.php';


// Funzione per arrotondare l'ora al quarto d'ora
function roundToQuarterHour($now){
    $minutes = $now['minutes'] - $now['minutes'] % 15;
    return sprintf(
        '%02d/%02d/%02d<br>%02d:%02d',
        $now["mday"], $now["mon"], substr($now["year"], -2), $now['hours'], $minutes
    );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema di monitoraggio">
    <meta name="author" content="Simone">
    <title>Gestione Emergenze - <?php echo htmlspecialchars($subtitle); ?></title>

	<style>    
		iframe{display: none;}
	</style>

	<script src="scripts/mire.js" defer></script>
</head>

<body>
    <div id="wrapper">
		
		<!-- Navbar -->
        <div id="navbar1"><?php require('navbar_up.php');?></div>  
        <?php require('./navbar_left.php')?> 

		<!-- Contenuto Pagina -->
        <div id="page-wrapper">
			<div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header noprint">
						Elenco punti di monitoraggio a lettura ottica (ultime 6 ore)
						<button class="btn btn-info noprint" onclick="printClass('fixed-table-container')">
							<i class="fa fa-print" aria-hidden="true"></i> Stampa tabella 
						</button>
					</h1>
                </div>
            </div>
            <div class="row">					
			
				<!-- Form di input -->
				<form action="" onsubmit="return clickButton();">
                    <?php
                    // Determinazione del percorso operativo
                    $perc = '';
                    if ($descrizione_foc == 'Attenzione') $perc = 'perc_al_g';
                    else if ($descrizione_foc == 'Pre-allarme') $perc = 'perc_al_a';
                    else if ($descrizione_foc == 'Allarme') $perc = 'perc_al_r';
                    ?>

					<!-- Campo Percorso -->
					<div class="form-group col-lg-4">
                        <label for="tipo">Percorso
                            <?php echo ($perc) ? '<font color="red">*</font>' : ''; ?>
                        </label>
                        <select class="form-control" onChange="getMira(this.value, '<?php echo $perc; ?>');" name="percorso" id="percorso" <?php echo ($perc) ? '' : 'disabled=""'; ?> required="">
                            <option name="percorso" value="NO"> ... </option>
                            <?php
                            $query_percorso = "SELECT ".$perc." FROM geodb.punti_monitoraggio_ok GROUP BY ".$perc." ORDER BY ".$perc.";";
							
                            $result_percorso = pg_query($conn, $query_percorso);  
                            while ($r_p = pg_fetch_assoc($result_percorso)) { 
                                $valore_percorso = $r_p[$perc];
                                $testo_percorso = $valore_percorso ? $valore_percorso : 'Punti fuori da percorsi prestabiliti';
                                echo "<option name='percorso' value='{$valore_percorso}'>{$testo_percorso}</option>";
                            }
                            ?>
                        </select>            
                        <small><?php echo ($perc) ? "Fase operativa comunale $descrizione_foc" : "Filtro percorsi solo se Fase Operativa Comunale in atto"; ?></small>
                    </div>

                    <!-- Campo Mira o Rivo -->
                    <?php 
                    if ($perc) { ?>
                        <div class="form-group col-lg-4">
                            <label for="mira">Mira o rivo:</label> <font color="red">*</font>
                            <select class="form-control" name="mira[]" id="mira" multiple size="5" required="" >
                               <!-- Questo menu a tendina viene popolato dinamicamente tramite la funzione getMira -->
                            </select>
							<div></div>
							<small>Le mire saranno caricate in base al percorso selezionato.</small>            
                        </div>
                    <?php } ?>

                    <!-- Campo Lettura -->
                    <div class="form-group col-lg-4">
                        <label for="tipo">Valore lettura mira:</label> <font color="red">*</font>
                        <select class="form-control" name="tipo" id="tipo" required="">
                            <option name="tipo" value=""> ... </option>
                            <?php            
                            $query_tipo_lettura = "SELECT id, descrizione FROM geodb.tipo_lettura_mire WHERE valido='t';";
                            $result_tipo_lettura = pg_query($conn, $query_tipo_lettura);  
                            while ($r_tipo = pg_fetch_assoc($result_tipo_lettura)) { 
                                echo "<option name='tipo' value='{$r_tipo['id']}'>{$r_tipo['descrizione']}</option>";
                            } ?>
                        </select>            
                    </div>

                    <!-- Pulsante di Invio -->
                    <div class="form-group col-lg-4">
                        <input name="conferma2" id="conferma2" type="submit" class="btn btn-primary" value="Inserisci letture">
                    </div>
                </form>

				<?php
				if(isset($_POST["conferma2"])){ 
					$ids=$_POST["mira"];
					$tipo_lettura = $_POST["tipo"];
					

					if ($_POST["data_inizio"]==''){
						date_default_timezone_set('Europe/Rome');
						$data_inizio = date('Y-m-d H:i');
					} else{
						$data_inizio=$_POST["data_inizio"].' '.$_POST["hh_start"].':'.$_POST["mm_start"];
					}
					
					// creo una lista di valori da inserire a db
					$values = [];

					// popolo la lista di valori
					foreach ($ids as $id) {
						$id=str_replace("'", "", $id);
						$values[] = "($id, $tipo_lettura, '$data_inizio')";
					}
					
					//costruisco la query di inserimento
					if (!empty($values)) {
						$values_str = implode(", ", $values);
						$query = "INSERT INTO geodb.lettura_mire (num_id_mira, id_lettura, data_ora) VALUES $values_str;";
						$result = pg_query($conn, $query);

						if (!$result) {
							echo "Errore durante l'inserimento delle letture: " . pg_last_error($conn);
						} else {
							$mire_ids_str = implode(",", $mire_ids);
							$query_log = "INSERT INTO varie.t_log (schema, operatore, operazione) VALUES ('geodb', '" . $_SESSION["Utente"] . "', 'Inserite letture per le mire: $mire_ids_str');";
							
							$result_log = pg_query($conn, $query_log);

							if (!$result_log) {
								echo "Errore durante il log dell'operazione: " . pg_last_error($conn);
							} 
						}				
					}
				}
				?>  
               	<hr>
				<div class="row">
					<?php
					

					$now = getdate();
					$ora0 = roundToQuarterHour($now);
					echo "<br><br>";
					$data = getdate(strtotime('- 30 minutes'));
					$ora1 = roundToQuarterHour($data);
					
					$data = getdate(strtotime('- 90 minutes'));
					$ora2 = roundToQuarterHour($data);
					
					$data = getdate(strtotime('- 150 minutes'));
					$ora3 = roundToQuarterHour($data);
					
					$data = getdate(strtotime('- 210 minutes'));
					$ora4 = roundToQuarterHour($data);
					
					$data = getdate(strtotime('- 270 minutes'));
					$ora5 = roundToQuarterHour($data);
					
					$data = getdate(strtotime('- 330 minutes'));
					$ora6 = roundToQuarterHour($data);
					
					?>
				
				</div>
				<style>
				@media print{
				   .fixed-table-toolbar{
					   display:none;
				   }
				}
				</style>
				<div class="row">
				<div class="noprint" id="toolbar">
					<select class="form-control noprint">
						<option value="">Esporta i dati visualizzati</option>
						<option value="all">Esporta tutto (lento)</option>
						<option value="selected">Esporta solo selezionati</option>
					</select>
				</div>
				<div id="tabella">
				<table  id="t_mire" class="table-hover" data-toggle="table" data-url="./tables/griglia_mire.php" 
					data-show-search-clear-button="true"   data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
					data-search="true" data-click-to-select="true" data-show-print="true"  
					data-pagination="true" data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
					data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
					data-filter-control="true" data-toolbar="#toolbar">
        
					<thead>
						<tr>
						<th class="noprint" data-field="state" data-checkbox="true"></th>    
						<th data-field="nome" data-sortable="true" data-visible="true" data-filter-control="input">Rio</th>
						<th data-field="tipo" data-sortable="true" data-visible="true" data-filter-control="select">Tipo</th>
						
						<th data-field="perc_al_g" data-sortable="true" 
							<?php if ($perc!='perc_al_g'){?> data-visible="false" <?php }?>
							data-filter-control="select">
							<i class="fas fa-location-arrow" title="Percorso allerta gialla" style="color:#ffd800;"></i>
						</th>
						<th data-field="perc_al_a" data-sortable="true" 
							<?php if ($perc!='perc_al_a'){?> data-visible="false" <?php }?>
							data-filter-control="select">
							<i class="fas fa-location-arrow" title="Percoso allerta arancione" style="color:#ff8c00;"></i>
						</th>
						<th data-field="perc_al_r" data-sortable="true" 
							<?php if ($perc!='perc_al_r'){?> data-visible="false" <?php }?>
							data-filter-control="select">
							<i class="fas fa-location-arrow" title="Percorso allerta rossa" style="color:#e00000;"></i>
						</th>

						<th data-field="arancio" data-sortable="true" data-visible="false"> Liv arancione</th>
						<th data-field="rosso" data-sortable="true" data-visible="false" >Liv rosso</th>

						<th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
						<th data-field="6" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora6;?></th>
						<th data-field="5" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora5;?></th>            
						<th data-field="4" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora4;?></th>
						<th data-field="3" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora3?></th>  
						<th data-field="2" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora2;?></th>
						<th data-field="1" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora1;?></th>
						<th data-field="0" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><?php echo $ora0;?></th>
						<th class="noprint" data-field="id" data-sortable="false" data-formatter="nameFormatterInsert" data-visible="true">Edit</th>
						</tr>
					</thead>
				</table>
</div>	
	
	
	
	
<!-- <i class="fas fa-search-plus"></i> -->
<?php
$query="SELECT p.nome,p.id 
FROM geodb.punti_monitoraggio_ok p
WHERE p.tipo ilike 'mira' OR p.tipo ilike 'rivo';";

$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
?>
	<div id="new_lettura<?php echo htmlspecialchars($r['id']); ?>" class="modal fade" role="dialog">
		<div class="modal-dialog">
			<!-- Modal content-->
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Inserire lettura <?php echo htmlspecialchars($r['nome']); ?></h4>
				</div>
				<div class="modal-body">
					<form autocomplete="off" action="./eventi/nuova_lettura.php?id=<?php echo urlencode($r['id']); ?>" method="POST">
						<div class="form-group">
							<label for="tipo">Valore lettura mira:</label> <font color="red">*</font>
							<select class="form-control" name="tipo" id="tipo" required>
								<option value=""> ... </option>
								<?php
								// Query per ottenere i tipi di lettura
								$queryTipo = "SELECT id, descrizione FROM geodb.tipo_lettura_mire WHERE valido='t';";
								$resultTipo = pg_query($conn, $queryTipo);

								while ($tipo = pg_fetch_assoc($resultTipo)) {
									echo '<option value="' . htmlspecialchars($tipo['id']) . '">' . htmlspecialchars($tipo['descrizione']) . '</option>';
								}
								?>
							</select>
						</div>

						<div class="form-group">
							<label for="hh_start">Ora:</label>
							<select class="form-control" name="hh_start" id="hh_start" required>
								<?php
								for ($j = 0; $j <= 24; $j++) {
									echo '<option value="' . str_pad($j, 2, '0', STR_PAD_LEFT) . '">' . str_pad($j, 2, '0', STR_PAD_LEFT) . '</option>';
								}
								?>
							</select>
						</div>

						<div class="form-group">
							<label for="mm_start">Minuti:</label>
							<select class="form-control" name="mm_start" id="mm_start" required>
								<?php
								for ($j = 0; $j < 60; $j += 5) {
									echo '<option value="' . str_pad($j, 2, '0', STR_PAD_LEFT) . '">' . str_pad($j, 2, '0', STR_PAD_LEFT) . '</option>';
								}
								?>
							</select>
						</div>

						<button id="conferma" type="submit" class="btn btn-primary">Inserisci lettura</button>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
				</div>
			</div>
		</div>
	</div>

	<script type="text/javascript" >
	$(document).ready(function() {
		$('#js-date<?php echo $r["id"]; ?>').datepicker({
			format: "yyyy-mm-dd",
			clearBtn: true,
			autoclose: true,
			todayHighlight: true
		});
	});
	</script>

	<?php } ?>


	<?php
	$query0="SELECT name, shortcode FROM geodb.tipo_idrometri_arpa;";
	$result0 = pg_query($conn, $query0);
	while($r0 = pg_fetch_assoc($result0)) {
	?>


	<div id="grafico_i_a<?php echo $r0['shortcode']; ?>" class="modal fade" role="dialog">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Grafico <?php echo $r0['name']; ?></h4>
		  </div>
		  <div class="modal-body">
				<?php 
				$idrometro=$r0["shortcode"];
				require('./grafici_idrometri_arpa.php'); 
				?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
		  </div>
		</div>
	  </div>
	</div>
	<?php } 

	$query0="SELECT nome, id FROM geodb.tipo_idrometri_comune WHERE usato='t';";
	$result0 = pg_query($conn, $query0);
	while($r0 = pg_fetch_assoc($result0)) {
	?>
	<div id="grafico_i_c<?php echo $r0['id']; ?>" class="modal fade" role="dialog">
	  <div class="modal-dialog">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Grafico <?php echo $r0['nome']; ?></h4>
		  </div>
		  <div class="modal-body">
				<?php 
				$idrometro=$r0["id"];
				require('./grafici_idrometri_comune.php'); 
				?>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
		  </div>
		</div>
	  </div>
	</div>

	<?php } ?>

	<div class="form-group col-lg-4">
		<label for="tipo">Valore lettura mira 2:</label> <font color="red">*</font>
		<select class="form-control" name="tipo" id="tipo2" required="">
			<option name="tipo" value=""> ... </option>
			<?php            
			$query_tipo_lettura = "SELECT id, descrizione FROM geodb.tipo_lettura_mire WHERE valido='t';";
			$result_tipo_lettura = pg_query($conn, $query_tipo_lettura);  
			while ($r_tipo = pg_fetch_assoc($result_tipo_lettura)) { 
				echo "<option name='tipo' value='{$r_tipo['id']}'>{$r_tipo['descrizione']}</option>";
			} ?>
		</select>            
	</div>

	<br><br>

	<button id="aggiornaSelezionati" class="btn btn-success" onclick="clickButton2()">Aggiorna selezionati</button>


	<br><br>

	<?php 
	require('./footer.php');
	require('./req_bottom.php');
	?>    

</body>

</html>
