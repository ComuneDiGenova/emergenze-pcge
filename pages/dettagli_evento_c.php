<?php 

$subtitle="Dettagli eventi in chiusura"

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

        <?php 
            require('./navbar_up.php')
        ?>  
        <?php 
            require('./navbar_left.php')
        ?> 
            

        <div id="page-wrapper">

            <br>
                
               <?php
			   #echo "<h1>TEst: ".$check_evento_c."</h1>";
               if ($check_evento_c==1){
					$len=count($eventi_attivi_c);	               
				    #echo $len;
					for ($k=0;$k<$len;$k++){
						#echo $eventi_attivi_c[$k]."<br>";
						 if ($eventi_attivi_c[$k]==$evento_attivo){
							 $i=$k;
							 #echo $i;
						 }
					}
					// for ($i=0;$i<$len;$i++){
	               	echo '<div class="row">';
	               	echo '<div class="col-lg-5"><h2><i class="fa fa-chevron-circle-down"></i> Evento in chiusura <small>(id='.$eventi_attivi_c[$i].')</small> ';
	               	echo ' - <a href="reportistica.php?id='.$eventi_attivi_c[$i].'" class="btn btn-info">Riepilogo';
					if($profilo_sistema<=2){
						echo ' (stampa report)';
					}
					echo '</a></h2></div>';
	   					echo '<div class="col-lg-4"><div style="text-align: center;"><h3 id=timer'.$i.' > </h3></div></div>';
	   					$check_segnalazioni=0;
	   					$query="SELECT id FROM segnalazioni.v_segnalazioni where id_evento=".$eventi_attivi_c[$i]." and (in_lavorazione='t' OR in_lavorazione is null) ";
	   					$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {
								$check_segnalazioni=1;
							}
	   					
	   					
	   					?>
	   					<?php //echo $start[$i]; ?>
							<script>
							// Set the date we're counting down to
							//var countDownDate = new Date("Jan 5, 2019 15:37:25").getTime();
							var countDownDate<?php echo $i; ?> = new Date("<?php echo $start_c[$i]; ?>").getTime();
							
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
	   					
	   					
	   					echo '<div class="col-lg-3"><br>';
						
						if ($profilo_sistema <= 2){
						//sospensione
							//echo $sospensione[$i];
							//echo " - OGGI:";
							date_default_timezone_set('Europe/Rome');  // Set timezone.

							$now_time=date("Y/m/d H:i:s");
							//echo $now_time;
							$oggi = strtotime($now_time);
							$dataScadenza = strtotime($sospensione_c[$i]);
							//echo " - OGGI:";
							//echo $oggi;
							//echo " - DATA SCADENZA:";
							//echo $dataScadenza;
							//echo " - " ;
							if ($sospensione_c[$i]=='' or $dataScadenza < $oggi){
								echo '<button type="button" class="btn btn-warning" title="Sospendi evento per 8 ore. Le segnalazioni legate all\'evento sospeso non saranno visibili in mappa e nell\'elenco della prima pagina"';	
								echo 'onclick="return sospendi'.$eventi_attivi_c[$i].'()"><i class="fas fa-pause"></i> 8h</button> - ';
							} else {
								echo '<i class="fas fa-pause" title="Evento sospeso per 8 ore fino al '.$sospensione_c[$i].'"></i> ';
								echo '<button type="button" class="btn btn-success" title="Anticipa la riapertura dell\'evento sospeso fino al '.$sospensione_c[$i].'" ';	
								echo 'onclick="return riprendi'.$eventi_attivi_c[$i].'()"><i class="fas fa-play"></i></button> - ';
							}
							?>
							<p id="msg"></p>
							<script type="text/javascript" >
							function sospendi<?php echo $eventi_attivi_c[$i];?>() {
								//alert('Test1');
								//var tel=document.getElementById('telsq<?php echo $m;?>').value;
								//var dataString='tel='+tel;
								$.ajax({
									type:"post",
									url:"./eventi/sospendi.php?id=<?php echo $eventi_attivi_c[$i];?>",
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
								//$('#sospensione<?php echo $eventi_attivi[$i];?>').load(document.URL +  ' #sospensione<?php echo $eventi_attivi[$i];?>');
								return false;
								
							};
							function riprendi<?php echo $eventi_attivi_c[$i];?>() {
								//alert('Test1');
								//var tel=document.getElementById('telsq<?php echo $m;?>').value;
								//var dataString='tel='+tel;
								$.ajax({
									type:"post",
									url:"./eventi/riprendi.php?id=<?php echo $eventi_attivi_c[$i];?>",
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
								//$('#sospensione<?php echo $eventi_attivi[$i];?>').load(document.URL +  ' #sospensione<?php echo $eventi_attivi[$i];?>');
								return false;
							};
							</script>
							<?php
	   					echo '<button type="button" class="btn btn-danger"  data-toggle="modal" data-target="#chiudi'.$eventi_attivi_c[$i].'"';
	   					if($check_segnalazioni) {
	   						echo 'disabled=""';
	   					}
	   					echo '><i class="fas fa-times"></i> Chiudi evento</button>';
	   					}
	   					echo '</div></div>';
	   					echo '<div class="row">';
	   					echo '<div class="col-lg-6"><h3>Tipologia: '.$i.' '. $tipo_eventi_c[$i][1].'</h3>';
							echo '<h3>Note: '. $nota_eventi_c[$i][1].'</h3>';
	   					echo '</div><div class="col-lg-6"><h3>Municipi interessati: ';
	   					$len2=count($municipi_c);
	   					//echo $len2;	               
	               	$k=0;
	               	for ($j=0;$j<$len2;$j++){
	               		
	               		if ($municipi_c[$j][0]==$eventi_attivi_c[$i]){
	               			if ($k==0) {echo $municipi_c[$j][1];} else {echo ', '.$municipi_c[$j][1];};
	               			$k=$k+1;
	               		}
	               	}
	   					//echo '</h3>Qua ci vuole la mappa dei municipi interessati';
	   					echo '</div></div><hr>';
	   					if($check_segnalazioni) {
	   						echo '<div align="center"><a class="btn btn-info" href="./elenco_segnalazioni.php">Vai alle segnalazioni non ancora chiuse</a></div><hr>';
	   					}
	   					
	   					?>
	   					
	   					
							<div class="row">
							<div style="text-align: center;">
								<h4> Storico allerte e Fasi Operative Comunali </h4><br>
							</div>
							<div class="col-lg-6">
							<?php
							$check_allerte=0;
							$check_foc=0;
							$query="SELECT * FROM eventi.v_allerte WHERE id_evento=".$eventi_attivi_c[$i]." and data_ora_fine_allerta <= now();";
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
								        <a data-toggle="collapse" href="#collapse_allerte<?php echo $eventi_attivi_c[$i];?>">Allerte passate</a>
								      </h4>
								    </div>
								    <div id="collapse_allerte<?php echo $eventi_attivi_c[$i];?>" class="panel-collapse collapse">
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
	   			
	   					
					



	   					$query="SELECT * FROM eventi.v_foc WHERE id_evento=".$eventi_attivi_c[$i]." and data_ora_fine_foc <= now();";
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
								        <a data-toggle="collapse" href="#collapse_foc<?php echo $eventi_attivi_c[$i];?>">Fasi Operative Comunali passate</a>
								      </h4>
								    </div>
								    <div id="collapse_foc<?php echo $eventi_attivi_c[$i];?>" class="panel-collapse collapse">
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
					</div> <!-- Chiudo la row -->

					<div class="row">
<hr>
<?php
	   					$query="SELECT * FROM eventi.t_attivazione_nverde WHERE id_evento=".$eventi_attivi_c[$i]." and data_ora_fine <= now();";
	   					//echo $query;
							//exit;
							$result = pg_query($conn, $query);
							while($r = pg_fetch_assoc($result)) {
								$check_nverde=2;
							}
							if($check_nverde==2) {
								//echo "<h3> Fasi Operative Comunali passate:</h3><ul>";
								?>
								<div class="panel-group">
  								<div class="panel panel-success">
								    <div class="panel-heading">
								      <h4 class="panel-title">
								        <a data-toggle="collapse" href="#collapse_nverde<?php echo $eventi_attivi_c[$i];?>">Attivazioni passate numero verde</a>
								      </h4>
								    </div>
								    <div id="collapse_nverde<?php echo $eventi_attivi_c[$i];?>" class="panel-collapse collapse">
								      <div class="panel-body">
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
								echo "<li> <i class=\"fas fa-circle fa-1x\" style=\"color:".$color."\007c37\"></i>  <b>Numero verde  attivo</b> dalle ".$ora_start." di ".$data_start." alle ore " .$ora_end ." di ".$data_end. " </li>";
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
							
	   					if($check_nverde==0) { echo "<ul><li>Nessuna attivazione del numero verde</li></ul>";}
	   			
	   					
					?>


					</div> <!-- Chiudo la row -->

<!-- Modal chiusura-->
<div id="chiudi<?php echo $eventi_attivi_c[$i]; ?>" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Chiudi evento definitivamente</h4>
      </div>
      <div class="modal-body">
      

        <form autocomplete="off" action="eventi/chiudi_evento.php?id='<?php echo $eventi_attivi_c[$i]?>'" method="POST">
		
		<?php if($check_allerte!=1) { ?>
		Se intendi proseguire l'evento verrà chiuso definitivamente. Non sarà più possibile modificare l'iter delle segnalazioni in corso, 
		ma solo consultare alcuni dati statistici.
		<hr>
		<label for="cat" class="auto-length">
			<input type="checkbox" name="cat<?php echo $i; ?>" id="cat<?php echo $i; ?>">
			Cliccare qua per confermare la chiusura dell'evento 
		</label>
		<?php } else { ?>
			Ci sono allerte in corso. Non è possibile chiudere l'evento.
		<?php } ?>
		<br><br>



        <button disabled="" id="conferma<?php echo $i; ?>" type="submit" class="btn btn-danger">Conferma chiusura evento</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>            			




<?php
//echo "</div>";
echo '<hr style="border:2px solid #000;">';

?>



<?php


		   				//}
	   			} else {
						echo "<h1> Nessun evento in fase di chiusura. </h1>";	   				
	   				
	   				
	   				
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
if ($contatore_eventi_chiusura > 0){
$len=$contatore_eventi_chiusura;	               
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
    
});

<?php }} ?>

}(jQuery));  
     
 </script>   

</body>

</html>
