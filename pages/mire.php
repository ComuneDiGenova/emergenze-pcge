<?php 

$subtitle = "Monitoraggio corsi d'acqua";
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

				<!-- JavaScript per invio dati -->
				<script type="text/javascript">  
					function clickButton() {
						var mira=document.getElementById('mira').value;
						var tipo=document.getElementById('tipo').value;
						var url ="eventi/nuova_lettura3.php?mira="+encodeURIComponent(mira)+"&tipo="+encodeURIComponent(tipo)+"";

						let http = new XMLHttpRequest(); 
						http.open("GET", url, true);
						http.send(null);

						$('#percorso').val('NO');
						$('#mira').val('');
						$('#tipo').val('');
						$('#t_mire').bootstrapTable('refresh', {silent: true});

						return false;
					}

					function getMira(val) {
						$.ajax({
							type: "POST",
							url: "get_mira.php",
							data:{ 'cod': val, 'f': "<?php echo $perc ?>" },
							success: function(data){
								$("#mira").html(data);
							}
						});
						return false;
					}
				</script>
			
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
                        <select class="form-control" onChange="getMira(this.value);" name="percorso" id="percorso" <?php echo ($perc) ? '' : 'disabled=""'; ?> required="">
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
                            <select class="form-control" name="mira" id="mira" required="">
                                <option value=""> Seleziona la mira </option>
                                <?php
                                $query_mire = "SELECT p.id, concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome FROM geodb.punti_monitoraggio_ok p WHERE p.id IS NOT NULL ORDER BY nome;";
                                $result_mire = pg_query($conn, $query_mire);    
                                while ($r_mire = pg_fetch_assoc($result_mire)) { 
                                    echo "<option name='mira' value='{$r_mire['id']}'>{$r_mire['nome']}</option>";
                                }
                                ?>
                            </select>            
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
                    <div class="row">
                        <input name="conferma2" id="conferma2" type="submit" class="btn btn-primary" value="Inserisci lettura">
                    </div>
                </form>

			<?php
             if(isset($_POST["conferma2"])){ 
				$id=$_POST["mira"];
				$id=str_replace("'", "", $id);

				if ($_POST["data_inizio"]==''){
					date_default_timezone_set('Europe/Rome');
					$data_inizio = date('Y-m-d H:i');
				} else{
					$data_inizio=$_POST["data_inizio"].' '.$_POST["hh_start"].':'.$_POST["mm_start"];
				}




				$query="INSERT INTO geodb.lettura_mire (num_id_mira,id_lettura,data_ora) VALUES(".$id.",".$_POST["tipo"].",'".$data_inizio."');"; 

				$result = pg_query($conn, $query);

				$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('geodb','".$_SESSION["Utente"] ."', 'Inserita lettura mira . ".$id."');";
				$result = pg_query($conn, $query_log);

              
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


<script>
function nameFormatterInsert(value, row) {
	if(row.tipo != 'IDROMETRO COMUNE' && row.tipo != 'IDROMETRO ARPA'){
		return' <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#new_lettura'+value+'">\
		<i class="fas fa-search-plus" title="Aggiungi lettura per '+row.nome+'"></i></button> - \
		<a class="btn btn-info" target=”_blank” href="mira.php?id='+value+'"> <i class="fas fa-chart-line" title=Visualizza ed edita dati storici></i></a>';
	} else if (row.tipo=='IDROMETRO ARPA') {
		return' <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#grafico_i_a'+value+'">\
		<i class="fas fa-chart-line" title="Visualizza grafico idro lettura per '+row.nome+'"></i></button>';
	 } else if (row.tipo=='IDROMETRO COMUNE') {
		return' <button type="button" class="btn btn-info noprint" data-toggle="modal" data-target="#grafico_i_c'+value+'">\
		<i class="fas fa-chart-line" title="Visualizza grafico idro lettura per '+row.nome+'"></i></button>';
	 }
}


function nameFormatterLettura(value,row) {
	if(row.tipo=='IDROMETRO ARPA' ){
		<?php
		$query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_arpa WHERE cod='?>row.id<?php';";
		$result_soglie = pg_query($conn, $query_soglie);
		while($r_soglie = pg_fetch_assoc($result_soglie)) {
			$arancio=$r_soglie['liv_arancione'];
			$rosso=$r_soglie['liv_rosso'];
		}
		?>
		if(value < row.arancio ){
			return '<font style="color:#00bb2d;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.arancio && value < row.rosso) {
			return '<font style="color:#FFC020;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.rosso) {
			return '<font style="color:#cb3234;">'+Math.round(value*1000)/1000+'</font>';
		} else {
			return '-';
		}
	} else if(row.tipo=='IDROMETRO COMUNE'){
	//	return Math.round(value*1000)/1000;
		<?php
		$query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_comune WHERE id='?>row.id<?php';";
		$result_soglie = pg_query($conn, $query_soglie);
		while($r_soglie = pg_fetch_assoc($result_soglie)) {
			$arancio=$r_soglie['liv_arancione'];
			$rosso=$r_soglie['liv_rosso'];
		}
		?>
		if(value < row.arancio ){
			return '<font style="color:#00bb2d;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.arancio && value < row.rosso) {
			return '<font style="color:#FFC020;">'+Math.round(value*1000)/1000+'</font>';
		} else if (value > row.rosso) {
			return '<font style="color:#cb3234;">'+Math.round(value*1000)/1000+'</font>';
		} else {
			return '-';
		}
	} else {
		if(value==1){
			return '<i class="fas fa-circle" title="Livello basso" style="color:#00bb2d;"></i>';
		} else if (value==2) {
			return '<i class="fas fa-circle" title="Livello medio" style="color:#ffff00;"></i>';
		} else if (value==3) {
			return '<i class="fas fa-circle" title="Livello alto" style="color:#cb3234;"></i>';
		} else {
			return '-';
		}
	}		
}

</script>
</div>	
	
	
	
	
<i class="fas fa-search-plus"></i>
<?php
$query="SELECT p.nome,p.id 
FROM geodb.punti_monitoraggio_ok p
WHERE p.tipo ilike 'mira' OR p.tipo ilike 'rivo';";

$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
?>
	<!-- Modal nuova lettura-->
	<div id="new_lettura<?php echo $r['id']; ?>" class="modal fade" role="dialog">
	  <div class="modal-dialog">
		<!-- Modal content-->
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal">&times;</button>
			<h4 class="modal-title">Inserire lettura <?php echo $r['nome']; ?></h4>
		  </div>
		  <div class="modal-body">
		  <form autocomplete="off" action="./eventi/nuova_lettura.php?id='<?php echo $r['id']; ?>'" method="POST">
			   <div class="form-group">
				  <label for="tipo">Valore lettura mira:</label> <font color="red">*</font>
								<select class="form-control" name="tipo" id="tipo" required="">
								<option name="tipo" value="" > ... </option>
				<?php            
				$query2="SELECT id,descrizione,rgb_hex From \"geodb\".\"tipo_lettura_mire\" WHERE valido='t';";
				$result2 = pg_query($conn, $query2);
				//echo $query1;    
				while($r2 = pg_fetch_assoc($result2)) { 
				?>    
						<option name="tipo" value="<?php echo $r2['id'];?>"><?php echo $r2['descrizione'];?></option>
				 <?php } ?>
				 </select>            
				 </div>
				
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
						  $start_date = 5;
						  $end_date   = 59;
						  $incremento = 5; 
						  for( $j=$start_date; $j<=$end_date; $j+=$incremento) {
							if($j<10) {
								echo '<option value="0'.$j.'">0'.$j.'</option>';
							} else {
								echo '<option value="'.$j.'">'.$j.'</option>';
							}
						  }
						?>

					
			<button  id="conferma" type="submit" class="btn btn-primary">Inserisci lettura</button>
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


				
				

            </div>


            
            <br><br>
            <div class="row">

            </div>

    </div>



<?php 

require('./footer.php');

require('./req_bottom.php');


?>


    

</body>

</html>
