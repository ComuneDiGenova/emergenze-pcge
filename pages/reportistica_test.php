<?php 

$subtitle="Reportistica";




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
//require('./tables/griglia_dipendenti_save.php');
require('./req.php');
require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');
//require('./conn.php');

require('./check_evento.php');


?>


<style type="text/css">
            
            .panel-allerta {
				  border-color: <?php echo $color_allerta; ?>;
				}
				.panel-allerta > .panel-heading {
				  border-color: <?php echo $color_allerta; ?>;
				  color: white;
				  background-color: <?php echo $color_allerta; ?>;
				}
				.panel-allerta > a {
				  color: <?php echo $color_allerta; ?>;
				}
				.panel-allerta > a:hover {
				  color: #337ab7;
				  /* <?php echo $color_allerta; ?>;*/
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
            require('./navbar_left.php');
            
         

        ?> 
            

        <div id="page-wrapper">
            <div class="row">
                <!--div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div-->
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            
            <?php //echo $note_debug; ?>
           

            
            
            <!-- /.row -->            
            <hr>
            
            <div class="row">
            <h3>Comunicazioni e informazioni alla popolazione</h3>
            <div class="col-lg-12">
            <h4>Attivazione numero verde: <?php echo $descrizione_nverde; ?></h4>
            
            </div>
            
            
            <div class="col-lg-6">
            <hr>
            <h4>Operatore numero verde
            
            <?php
				if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_oNV">
				<i class="fas fa-plus"></i> Aggiungi </button>
				</h4>
				
				<!-- Modal reperibilità-->
				<div id="new_oNV" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Inserire operatore Numero Verde</h4>
				      </div>
				      <div class="modal-body">
      

        <form autocomplete="on" action="report/nuovo_oNV.php" method="POST">
		
		
			
            <div class="form-group  ">
				  <label for="cf">Seleziona dipendente comunale:</label> <font color="red">*</font>
								<select name="cf" id="cf2" class="selectpicker show-tick form-control" data-live-search="true" required="">
								<!--option name="cf" id="cf2" value="">Seleziona personale</option-->
				

				 </select>            
				 
				 </div>
   
           
				<div class="form-group">
						<label for="data_inizio" >Data inizio (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_inizio" id="js-date9" required>
						<!--div class="input-group-addon" id="js-date" >
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
						<label for="data_fine" >Data fine (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_fine" id="js-date10" required>
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
		           
                  



        <button  id="conferma" type="submit" class="btn btn-primary">Inserisci operatore Numero Verde</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>
				
				
				
				<?php
				}  else {
					echo "</h4>";
				}//else if($profilo_sistema <3) {
				
				
				
				
				
			$query = "SELECT r.matricola_cf, u.cognome, u.nome, r.data_start, r.data_end from report.t_operatore_nverde r ";
			$query = $query. "JOIN varie.v_dipendenti u ON r.matricola_cf=u.matricola ";
			$query = $query. "where data_start < now() and data_end > now() ";
			//$query = $query. " and id1=".$r0["id1"]."";
			$query = $query. " order by cognome;";
			
			//echo $query;
			
			$check_reperibile=0;
			$result = pg_query($conn, $query);
			//echo "<ul>";
			while($r = pg_fetch_assoc($result)) { 
				$check_reperibile=1;
				//echo "<li>";
				echo "- ";
				echo  $r['cognome']." ".$r['nome']." - Dalle ";
				echo  $r['data_start']. " alle ".$r['data_end']. "<br>";
				
				
				//echo "</li>";
			}
			
			if ($check_reperibile==0){
				echo '- <i class="fas fa-circle" style="color: red;"></i> In questo momento non ci sono responsabili Monitoraggio Meteo <br>';
			}
			
			//echo "</ul>";
            
         ?> 
            </div>
            
           
            <div class="col-lg-6">
            <hr>
            <h4>Numero chiamate ricevute</h4>
            
            <?php 
            $query_e="SELECT e.id, tt.descrizione 
            FROM eventi.t_eventi e
            JOIN eventi.join_tipo_evento t ON t.id_evento=e.id
            JOIN eventi.tipo_evento tt on tt.id=t.id_tipo_evento
			 	WHERE e.valido != 'f'
			   GROUP BY e.id, tt.descrizione;";
             
            $result_e = pg_query($conn, $query_e);
				//echo "<ul>";
				while($r_e = pg_fetch_assoc($result_e)) { 
             
					$query="SELECT count(r.id)
					FROM segnalazioni.t_richieste_nverde r 
					WHERE r.id_evento = ".$r_e['id'].";";
					echo $query;
					$result = pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) { 
            		echo "<b>Richieste generiche (".$r_e['descrizione']."):</b>".$r['count'];
            	}
            
            
            }
            
            
            
            ?>
            
            
            </div>            
            </div>
            <!-- /.row -->            
            <hr>
            
            
            <?php 
             
            require('./conteggi_dashboard.php');
            
            require('./contatori.php');
            ?>
            
            <div class="row">
                
                
				<div class="col-lg-8">
                
                
                    <!--div id="panel-riepilogo" class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-bell fa-fw"></i> Pannello riepilogo
                        </div>
                        
                        <div class="panel-body">
                            <div class="list-group">
                               
                                		<?php if($segn_limbo>0){?>
                                			 <a href="#segn_limbo_table" class="list-group-item">
	                                    <i class="fa fa-exclamation fa-fw" style="color:red"></i> Nuove segnalazioni da elaborare!
	                                    <span class="pull-right text-muted small"><em><?php echo $segn_limbo; ?></em>
	                                    </span>
	                                    </a>
                                    <?php }?>
                                
								
											<?php if($inc_limbo>0){?>
                                			 <div class="list-group-item" >
	                                    <i class="fa fa-exclamation fa-fw" style="color:red"></i> Nuovi incarichi ancora da prendere in carico!
	                                    <span class="pull-right text-muted small"><em><?php echo $inc_limbo; ?></em>
	                                    </span>
	                                    
	                                    </div>
                                    <?php }?>
								
								<div class="list-group-item" >
											
                                
                                    <i class="fa fa-users"></i> <b>Gestione squadre</b>
                                    <br><br>
                                     - <i class="fa fa-play"></i> Squadre in azione
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_in_azione; ?></em>
                                    </span>
                                    
                                    <br>
                                     - <i class="fa fa-pause"></i> Squadre a disposizione
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_disposizione; ?></em>
                                    </span>
                                    <br>
                                     - <i class="fa fa-stop"></i> Squadre a riposo
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_riposo; ?></em>
                                    </span>
                                    <hr>
                                    Totale squadre eventi attivi:
                                    <span class="pull-right text-muted small"><em><?php echo $squadre_riposo; ?></em>
                                    </span>
                                </div>
                            
                            <a href="./gestione_squadre.php" class="btn btn-default btn-block">Vai alla gestione squadre</a>
							
							
							<div class="list-group-item" >
											
                                
                                    <i class="fa fa-pencil-ruler"></i> <b>Presidi</b>
                                    <br><br>
                                     - <i class="fa fa-pause"></i> Assegnati
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_assegnati; ?></em>
                                    </span>
                                    
                                    <br>
                                     - <i class="fa fa-play"></i> In corso
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_corso; ?></em>
                                    </span>
                                    <br>
                                     - <i class="fa fa-stop"></i> Conclusi
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_conclusi; ?></em>
                                    </span>
                                    <hr>
                                    Totale presidi eventi attivi:
                                    <span class="pull-right text-muted small"><em><?php echo $sopralluoghi_tot; ?></em>
                                    </span>
                                </div>
                            
                            <a href="./nuovo_sopralluogo.php" class="btn btn-default btn-block">Crea un nuovo presidio</a>
							
							</div>
							
							<div class="list-group-item" >
											
                                
                                    <i class="fa fa-exclamation-triangle"></i> <b>Provvedimenti cautelari</b>
                                    <br><br>
                                     - <i class="fa fa-pause"></i> Assegnati
                                    <span class="pull-right text-muted small"><em><?php echo $pc_assegnati; ?></em>
                                    </span>
                                    
                                    <br>
                                     - <i class="fa fa-play"></i> In corso
                                    <span class="pull-right text-muted small"><em><?php echo $pc_corso; ?></em>
                                    </span>
                                    <br>
                                     - <i class="fa fa-stop"></i> Portati a termine
                                    <span class="pull-right text-muted small"><em><?php echo $pc_conclusi; ?></em>
                                    </span>
                                    <hr>
                                    Totale provvedimenti cautelari eventi attivi:
                                    <span class="pull-right text-muted small"><em><?php echo $pc_tot; ?></em>
                                    </span>
                                </div>
                            

                            <a href="./elenco_pc.php" class="btn btn-default btn-block">Elenco provvedimenti cautelari</a>
							
							</div>
                        
						
						
						
						

                    </div-->


                    
                    
                    
                    
                    
            </div> 
            
            
            
              
            <div class="col-lg-4">
                
                
                <div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-traffic-light fa-fw"></i> Mappa ufficiale <a target="_new" href="http://www.allertaliguria.gov.it">allertaliguria</a> 
							 <div class="pull-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                        Altro
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu pull-right" role="menu">
                                        <li><a href="bollettini_meteo.php">Elenco bollettini allerte</a>
                                        </li>
                                        <li class="divider"></li>
                                        <li><a href="http://www.allertaliguria.gov.it">Vai alla pagina www.allertaliguria.gov.it </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                           
							  <img class="pull-right img-responsive" imageborder="0" alt="Problema di visualizzazione immagine causato da sito http://www.allertaliguria.gov.it/" src="https://mappe.comune.genova.it/allertaliguria/mappa_allerta_render.php">
                        </div>                    
                        <!-- /.panel-body -->
                    </div>
                
					<br>
				<a href="#mappa_segnalazioni" class="btn btn-info"> <i class="fas fa-file-pdf"></i> Tasto per creare report 1 (demo) </a>
				<br><hr><br>
				<a href="#mappa_segnalazioni" class="btn btn-info"> <i class="fas fa-file-pdf"></i> Tasto per creare report 2 (demo) </a>
				<br><br>
                    

            </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>

<script>

	/*var mymap = L.map('mapid').setView([44.411156, 8.932661], 12);

	L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw', {
		maxZoom: 18,
		attribution: 'Map data &copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors, ' +
			'<a href="https://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
			'Imagery © <a href="https://www.mapbox.com/">Mapbox</a>',
		id: 'mapbox.streets'
	}).addTo(mymap);

	L.marker([44.411156, 8.932661]).addTo(mymap)
		.bindPopup("<b>Hello world!</b><br />I am a leafletJS popup.").openPopup();




	var popup = L.popup();

	function onMapClick(e) {
		popup
			.setLatLng(e.latlng)
			.setContent("You clicked the map at " + e.latlng.toString())
			.openOn(mymap);
	}

	mymap.on('click', onMapClick);*/



  
$(document).ready(function() {
    $('#js-date').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date2').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
      $('#js-date3').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date4').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
     $('#js-date5').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date6').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
      $('#js-date7').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date8').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
    $('#js-date9').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });
    $('#js-date10').datepicker({
        format: "yyyy-mm-dd",
        clearBtn: true,
        autoclose: true,
        todayHighlight: true
    });  
    
    
});



/*var http = new XMLHttpRequest();
 var url = "./tables/griglia_dipendenti_save.php";
 //var params = "provincia="+ provincia;
 http.open("GET", url, true);
 http.onreadystatechange = function() {
  if(http.readyState === 4 && http.status === 200) {
      var response = http.responseText;
      var json = JSON.parse(response); //parsa la stringa JSON che restituisce lo script json_comuni.php
      //elimina options
      $("#cf2").children("option").remove(); // elimina tutte le select che ci sono già
	  
      for(var i=0; i<json.length;i++){// ciclo che riempie di nuovo la select con id "comune" con le nuove option
          $('#cf2').append('<option value="' + json[i].matricola + '">' + json[i].cognome + '</option>'); //nome è definito nella stringa JSON
      }
  }
 };
 http.send(null);*/


// secondo esempio 
var request = new XMLHttpRequest()

request.open('GET', './tables/griglia_dipendenti_save.php', true)

request.onload = function() {
  // begin accessing JSON data here
  var data = JSON.parse(this.response)
  //$("#cf2").children("option").remove(); // elimina tutte le select che ci sono già
  /*for (var i = 0; i < data.length; i++) {
    //console.log(data[i].matricola + ' is a ' + data[i].cognome + '.')
	$('#cf2').append('<option value="' + data[i].matricola + '">' + data[i].cognome + '</option>');
  }*/
	var $select = $('#cf2');
	$.each(data, function(i, val){
		$select.append($('<option />', { value: (i+1), text: val[i+1] }));
	});
	}

request.send()

</script>
    

</body>

</html>
