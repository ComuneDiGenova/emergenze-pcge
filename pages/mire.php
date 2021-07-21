<?php 

$subtitle="Monitoraggio corsi d'acqua "

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

/*function roundToQuarterHour($timestring) {
    $minutes = date('i', strtotime($timestring));
    return $minutes - ($minutes % 15);
}*/


function roundToQuarterHour($now){
	$minutes = $now['minutes'] - $now['minutes']%15;
	if ($minutes < 10) {
		$minutes = '0'.$minutes;
	}

	$rounded = $now["mday"]."/".$now["mon"]."/".substr($now["year"],-2)."<br>".$now['hours'].":".$minutes;
	return $rounded;
}

require('./req.php');

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

require('./check_evento.php');
?>
<style>    
	iframe{
  display: none;
}
</style>
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
             <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header noprint">Elenco punti di monitoraggio a lettura ottica (dati ultime 6 h)
					<button class="btn btn-info noprint" onclick="printClass('fixed-table-container')">
					<i class="fa fa-print" aria-hidden="true"></i> Stampa tabella </button>
					</h1>
                </div>
                </div>
               <div class="row">
           
            <script type="text/javascript">  
			function clickButton2() {
				var mira=document.getElementById('mira').value;
				var tipo=document.getElementById('tipo').value;
				//alert(mira);
				//alert(mira.value);
				var url ="eventi/nuova_lettura3.php?mira="+encodeURIComponent(mira)+"&tipo="+encodeURIComponent(tipo)+"";
				// get the URL
				http = new XMLHttpRequest(); 
				http.open("GET", url, true);
				http.send(null);

				//alert('Dato della mira inserito. Per visualizzare il dato aggiorna la tabella con l\'apposito tasto');
				//$('#msg').html(html);
				$('#percorso').val('NO');
				$('#mira').val('');
				$('#tipo').val('');
				$('#t_mire').bootstrapTable('refresh', {silent: true});

				// prevent form from submitting
				return false;
			}
			</script>
			
			<!--form-->    
	        <!--form name="form1" target="content" autocomplete="off" action="eventi/nuova_lettura2.php" method="POST" id="submit_form"-->
			<form action="" onsubmit="return clickButton2();">
			<?php
			if ($descrizione_foc=='-'){
				$perc='';
			}else if ($descrizione_foc=='Attenzione'){
				$perc='perc_al_g';
			} else if ($descrizione_foc=='Pre-allarme'){
				$perc='perc_al_a';
			} else if($descrizione_foc=='Allarme') { 
				$perc='perc_al_r';
			}
			?>
			
			<!--la funzione clickButton sembrerebbe essere non utilizzata quindi il file nuova_lettura2.php  è da ignorare-->
			<script type="text/javascript">
			function clickButton() {
			var mira=document.getElementById('mira').value;
			var tipo=document.getElementById('tipo').value;
			$.ajax({
					type:"post",
					url:"eventi/nuova_lettura2.php",
					data: 
					{  
					   'mira' :mira,
					   'tipo' :tipo
					},
					cache:false,
					success: function (html) 
					{
					   //alert('Dato della mira inserito. Per visualizzare il dato aggiorna la tabella con l\'apposito tasto');
					   $('#msg').html(html);
					   $('#mira').val('');
					   $('#tipo').val('');
					   $('#t_mire').bootstrapTable('refresh', {silent: true});
					}
					});
					return false;
			 }



				function getMira(val) {
					$.ajax({
					type: "POST",
					url: "get_mira.php",
					data:'cod='+val+'&f=<?php echo $perc?>',
					success: function(data){
						$("#mira").html(data);
					}
					});
					return false;
				}

			</script>
			
			
			
				<div class="form-group col-lg-4">
				<label for="tipo">Percorso
				<?php
				if ($perc==''){
					echo '</label>';
				} else {
					echo '</label><font color="red">*</font>';
				}
				?>
				
				
				<!--select class="selectpicker show-tick form-control" data-live-search="true" -->
				<select class="form-control"
				onChange="getMira(this.value);" name="percorso" id="percorso" required=""
				<?php 
				if ($perc==''){
					echo ' disabled=""';
				}
				?>
				>
				<option name="percorso" value="NO" > ... </option>
			   
			   <?php
	
				$query_percorso="SELECT ".$perc." 
				FROM geodb.punti_monitoraggio_ok 
				GROUP BY ".$perc." 
				ORDER BY ".$perc.";";

			   $result_percorso = pg_query($conn, $query_percorso);
				//echo $query1;    
				while($r_p = pg_fetch_assoc($result_percorso)) { 
					if ($r_p["$perc"] ==''){
					?>
						<option name="percorso" value="<?php echo $r_p["$perc"];?>">Punti fuori da percorsi prestabiliti</option>
					<?php
					} else {
					?>
						<option name="percorso" value="<?php echo $r_p["$perc"];?>"><?php echo $r_p["$perc"];?></option>
					<?php
					}
				} 
				?>
				 </select>            
				 <?php
				if ($perc==''){
					echo '<small>Filtro percorsi solo se Fase Operativa Comunale in atto</small>';
				} else {
					echo '<small>Fase operativa comunale '.$descrizione_foc.'):</small>';
				}
				?>
				</div>
	
				<?php 
				if ($perc!=''){
				?>
					<div class="form-group col-lg-4">
					<label for="mira">Mira o rivo:</label> <font color="red">*</font>
					<select class="form-control" name="mira" id="mira" class="demoInputBox" required="">
					<option value="" > Seleziona la mira </option>
				<?php
				} else {
				?>
					<div class="form-group col-lg-4">
					<label for="tipo">Mira o rivo:</label> <font color="red">*</font>
					<select class="selectpicker show-tick form-control" data-live-search="true" name="mira" id="mira" required="">
					<option value="" > ... </option>
				   
				   <?php
					$query_mire= "SELECT p.id, concat(p.nome,' (', replace(p.note,'LOCALITA',''),')') as nome
					FROM geodb.punti_monitoraggio_ok p
					WHERE p.id is not null 
					order by nome;";

				   $result_mire = pg_query($conn, $query_mire);
					//echo $query1;    
					while($r_mire = pg_fetch_assoc($result_mire)) { 
					?>    
							<option name="mira" value="<?php echo $r_mire['id'];?>"><?php echo $r_mire['nome'];?></option>
					<?php 
					} 
				}
				 ?>
					
				 
				 
				 </select>            
				 </div>
			   
			   
			   <div class="form-group col-lg-4">
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
				 </div>
             <div class="row">
			 <!-- molto  importante il return per non ricaricare la pagina!! -->
             <!--button  name="conferma2" id="conferma2" type="submit" onclick="return clickButton();" class="btn btn-primary">
			 Inserisci lettura</button-->
			 <input  name="conferma2" id="conferma2" type="submit" class="btn btn-primary" value="Inserisci lettura">
			 
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
					//$d1 = new DateTime($data_inizio);
					//$d2 = new DateTime($data_fine);
					//$d1 =  strtotime($data_inizio);
				}

				//echo $data_inizio;
				//echo "<br>";

				//echo $d1;
				//echo "<br>";



				$query="INSERT INTO geodb.lettura_mire (num_id_mira,id_lettura,data_ora) VALUES(".$id.",".$_POST["tipo"].",'".$data_inizio."');"; 
				//echo $query;
				//exit;
				$result = pg_query($conn, $query);
				//echo "<br>";





				//exit;



				$query_log= "INSERT INTO varie.t_log (schema,operatore, operazione) VALUES ('geodb','".$_SESSION["Utente"] ."', 'Inserita lettura mira . ".$id."');";
				$result = pg_query($conn, $query_log);



				//$idfascicolo=str_replace('A','',$idfascicolo);
				//$idfascicolo=str_replace('B','',$idfascicolo);
				//echo "<br>";
				//echo $query_log;

              
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
		<!--th data-field="id" data-sortable="true" data-visible="false" data-filter-control="select">Id</th-->
		<th data-field="perc_al_g" data-sortable="true" <?php if ($perc!='perc_al_g'){?> data-visible="false" <?php }?> data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta gialla" style="color:#ffd800;"></i></th>
		<th data-field="perc_al_a" data-sortable="true" <?php if ($perc!='perc_al_a'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percoso allerta arancione" style="color:#ff8c00;"></i></th>
		<th data-field="perc_al_r" data-sortable="true"  <?php if ($perc!='perc_al_r'){?> data-visible="false" <?php }?>data-filter-control="select"><i class="fas fa-location-arrow" title="Percorso allerta rossa" style="color:#e00000;"></i></th>
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
		<a class="btn btn-info" href="mira.php?id='+value+'"> <i class="fas fa-chart-line" title=Visualizza ed edita dati storici></i></a>';
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
				<!--div class="form-group">
					<label for="data_inizio" >Data lettura (AAAA-MM-GG) </label> <font color="red">*</font>                 
					<input type="text" class="form-control" name="data_inizio" id="js-date<?php echo $r["id"]; ?>" required>
				</div> 
				<div class="form-group">
					<label for="ora_inizio"> Ora lettura:</label> <font color="red">*</font>
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
					  </select>
					  </div>
					</div>  
					</div-->
					
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


    

</body>

</html>
