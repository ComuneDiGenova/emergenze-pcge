<?php 

$subtitle="Monitoraggio corsi d'acqua"

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
?>
    
</head>

<body>

    <div id="wrapper">

        <?php 
            require('./navbar_up.php')
        ?>  
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
				
				<?php
				//echo strtotime("now");
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s');
				//echo "<br><br>";
				//echo date('Y-m-d H:i:s')-3600;
				//echo "<br><br>";
				$now = new DateTime();
				$date = $now->modify('-1 hour')->format('Y-m-d H:i:s');
				//echo $date;
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
		<th data-field="arancio" data-sortable="true" data-visible="false" data-filter-control="select">Liv arancione</th>
		<th data-field="rosso" data-sortable="true" data-visible="false" data-filter-control="select">Liv rosso</th>
		<th data-field="last_update" data-sortable="false"  data-visible="true">Last update</th>
		<th data-field="6" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true">6-5 h</th>
		<th data-field="5" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true">5-4 h</th>            
		<th data-field="4" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true">4-3 h</th>
		<th data-field="3" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true">3-2 h</th>  
		<th data-field="2" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true">2-1 h</th>
		<th data-field="1" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true">1h -10'</th>
		<th data-field="1" data-sortable="false" data-formatter="nameFormatterLettura" data-visible="true"><10'</th>
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
	 }
}


function nameFormatterLettura(value,row) {
	if(row.tipo=='IDROMETRO ARPA'){
		<?php
		$query_soglie="SELECT liv_arancione, liv_rosso FROM geodb.soglie_idrometri_arpa WHERE cod='?>row.id<?php';";
		$result_soglie = pg_query($conn, $query_soglie);
		while($r_soglie = pg_fetch_assoc($result_soglie)) {
			$arancio=$r_soglie['liv_arancione'];
			$rosso=$r_soglie['liv_rosso'];
		}
		?>
		if(value < row.arancio ){
			return '<i class="fas fa-circle" style="color:#00bb2d;"></i></button>';
		} else if (value > row.arancio && value < row.rosso) {
			return '<i class="fas fa-circle" style="color:#ffff00;"></i></button>';
		} else if (value > row.rosso) {
			return '<i class="fas fa-circle" style="color:#cb3234;"></i></button>';
		} else {
			return '-';
		}
		
	} else if(row.tipo=='IDROMETRO COMUNE'){
		return Math.round(value*1000)/1000;
	} else {
		if(value==1){
			return '<i class="fas fa-circle" style="color:#00bb2d;"></i></button>';
		} else if (value==2) {
			return '<i class="fas fa-circle" style="color:#ffff00;"></i></button>';
		} else if (value==3) {
			return '<i class="fas fa-circle" style="color:#cb3234;"></i></button>';
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
	<!-- Modal allerta-->
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
				<div class="form-group">
							<label for="data_inizio" >Data lettura (AAAA-MM-GG) </label> <font color="red">*</font>                 
							<input type="text" class="form-control" name="data_inizio" id="js-date<?php echo $r["id"]; ?>" required>
						</div> 
						<div class="form-group"-->
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
					</div>
					
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
				require('./grafici_idrometri.php'); 
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
