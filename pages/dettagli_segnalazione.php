<?php 
// Start the session
session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;


$id=pg_escape_string($_GET["id"]);
$subtitle="Dettagli segnalazione ricevuta n. ".$id;

$check_segnalazione=1; // specifica che si tratta di una segnalazione (e.g per il panel_comunicazioni.php) 

$check_spostamento=1; // se 1 posso spostare in caso contrario diventa 0
						// diventa 0 se: 
						// ci sono elementi a rischio / provvedimenti cautelari associati
						// ci sono altre segnalazioni nelle vicinanze
						// altre ev. da aggiungere

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

$check_evento_aperto=1;
$query_evento_aperto="SELECT s.id,
       e.valido 
		FROM segnalazioni.t_segnalazioni s
		JOIN eventi.t_eventi e on e.id=s.id_evento
		WHERE s.id=".$id.";";

$result_e=pg_query($conn, $query_evento_aperto);
while($r_e = pg_fetch_assoc($result_e)) {
	if($r_e['valido']=='f') {
		$check_evento_aperto=0;
		$table='v_segnalazioni_eventi_chiusi_lista';
		//echo "false";
	} else {
		$table='v_segnalazioni';
		//echo "true";
	}
}

?>

 


<link rel="stylesheet" href="l_map/css/L.Control.Locate.min.css">
   <link rel="stylesheetl_map/" href="l_map/css/qgis2web.css">
   <link rel="stylesheet" href="l_map/css/MarkerCluster.css">
   <link rel="stylesheet" href="l_map/css/MarkerCluster.Default.css">
   <link rel="stylesheet" href="l_map/css/leaflet-measure.css">
   <link rel="stylesheet" href="../vendor/leaflet-search/src/leaflet-search.css">
        
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
            <!--div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Titolo pagina</h1>
                </div>
            </div-->
            <!-- /.row -->
            
            <!--br><br-->
            <div class="row">
            <div class="col-md-6">
				<?php
					$query= "SELECT *, st_x(st_transform(geom,4326)) as lon , st_y(st_transform(geom,4326)) as lat FROM segnalazioni.".$table." WHERE id=".$id.";";
					//echo $query
           
					$result=pg_query($conn, $query);
					while($r = pg_fetch_assoc($result)) {
						
						$lon=$r['lon'];
						$lat=$r['lat'];
						$id_civico=$r['id_civico'];
						$geom=$r['geom'];
						$id_municipio=$r['id_municipio'];
						$id_evento=$r['id_evento'];
						
					?>            
            
               <h4><br><b>Tipo criticità</b>: <?php echo $r['criticita']; ?></h4>
               <hr>
            	
						
						<?php 
            		$id_lavorazione=$r['id_lavorazione'];
						$check_lav=0;
						//$check_operatore=0; //controllo se l'operatore può fare qualcosa o meno
						$id_profilo=$r['id_profilo'];
						$id_municipio=$r['id_municipio'];
						$id_evento=$r['id_evento'];
						
						
						//echo 'Munic'. $id_municipio.'<br>';
						//echo 'Profilo:'. $profilo_sistema.'<br>';
						//echo 'id_uo:'. $id_uo.'<br>';
						require('./check_operatore.php');
						
						if ($r['id_lavorazione'] !='' and $r['in_lavorazione']=='t') {
									$check_lav=1;
									
									
									$check_mun=0;
									$query_sospeso="SELECT sospeso FROM segnalazioni.join_segnalazioni_in_lavorazione 
									WHERE id_segnalazione_in_lavorazione = ".$id_lavorazione." AND sospeso = 't';";
									$result_sospeso=pg_query($conn, $query_sospeso);
									while($r_sospeso = pg_fetch_assoc($result_sospeso)) {
										if ($check_operatore==0){
											echo '<h4>La segnalazione è stata inviata alla centrale di PC';
										} else {
										if($profilo_ok==3) {
											echo '<h4>La segnalazione è stata inviata alla centrale di PC';
											echo ' - <a class="btn btn-success" 
											href="segnalazioni/import_lavorazione_2mun.php?id='.$id.'&idl='.$id_lavorazione.'" 
											title="Prendi in carico"> <i class="fas fa-play"></i> </a>';
										}
										echo '</h4>';
										$check_mun=1;
									}
									}
									
									if($check_mun==0) {
										echo '<h4> <i class="fas fa-play"></i> La segnalazione è in lavorazione';
										echo '</h4>';
									}
									
									
									require('./check_responsabile.php');
									
									
								} else if ($r['id_lavorazione'] !=''  and $r['in_lavorazione']=='f') {
									
									$check_lav=-1;
									$check_spostamento=0;
									echo '<h4> <i class="fas fa-stop"></i> La segnalazione è chiusa </h4>';
									?>
									<h4><br><b>Note chiusura</b>: <?php echo $r['descrizione_chiusura']; ?></h4>
									<?php
									$query_ch="SELECT invio_manutenzioni, id_man 
									FROM segnalazioni.t_segnalazioni_in_lavorazione 
									WHERE id = ".$id_lavorazione." ;";
									//echo $query_ch;
									$result_ch=pg_query($conn, $query_ch);
									while($r_ch = pg_fetch_assoc($result_ch)) {
										if ($r_ch['invio_manutenzioni']=='t'){
											echo '<h4>Segnalazione inserita sul sistema manutenzioni
											 con id = '. $r_ch['id_man'].'</h4>';
										}
									}
						}
						?>
						<hr>
						<!--h4> Persona a rischio? </h4-->
						<h3><i class="fas fa-list-ul"></i> Dettagli segnalazione n. <?php echo $r['id'];?></h3>
						<?php 
						if($r['rischio'] =='t') {
							echo '<i class="fas fa-circle fa-1x" style="color:#ff0000"></i> Persona a rischio';
						} else if ($r['rischio'] =='f') {
							echo '<i class="fas fa-circle fa-1x" style="color:#008000"></i> Non ci sono persone a rischio';
						} else {
							echo '<i class="fas fa-circle fa-1x" style="color:#ffd800"></i> Non è specificato se ci siano persone a rischio';
						}
						?>
						<!--h4><i class="fas fa-list-ul"></i> Generalità </h4-->
						<br><b>Identificativo evento</b>: <?php echo $r['id_evento']; ?>
						<br><b>Descrizione</b>: <?php echo $r['descrizione']; ?>
						<br><b>Note</b>: <?php echo $r['note']; ?>
						<br><b>Data e ora inserimento</b>: <?php echo $r['data_ora']; ?>
						<!--br><b>Matricola operatore inserimento segnalazione</b>: <?php echo $r['id_operatore']; ?>-->
						<br><b>Tipologia operatore inserimento segnalazione</b>: <?php echo $r['uo_ins']; ?>
						<?php if ($profilo_ok=3){ 
						$query_u="select nome,cognome from users.v_utenti_sistema where matricola_cf='".$r['id_operatore']."';";
						$result_u=pg_query($conn, $query_u);
						while($r_u = pg_fetch_assoc($result_u)) {
						?>
							<br><b>Operatore inserimento</b>: <?php echo $r_u['cognome']; ?> <?php echo $r_u['nome']; ?>
							
							(<?php echo $r['id_operatore']; ?>)
						<?php }
						} 
						?>
						
						<br>
						<?php 
						$query_altre="SELECT * FROM segnalazioni.".$table." where id_lavorazione=".$id_lavorazione." and id <>".$r['id']."";
						//echo $query_altre;
						$result_altre=pg_query($conn, $query_altre);
						while($r_altre = pg_fetch_assoc($result_altre)) {
							$check_spostamento=0;
							echo '<br><br><a class="btn btn-info noprint" href="dettagli_segnalazione.php?id='.$r_altre["id"].'">Vai alla segnalazione congiunta (id='.$r_altre["id"].')</a>';
						}
						?>
						
						<!--div class="panel-group">
						  <div class="panel panel-default">
						    <div class="panel-heading">
						      <h4 class="panel-title">
						        <a data-toggle="collapse" href="#c_segnalante">Generalità segnalante</a>
						      </h4>
						    </div>
						    <div id="c_segnalante" class="panel-collapse collapse">
						      <div class="panel-body"-->
						      <hr>
						     <h4><i class="fas fa-user"></i> Segnalante </h4> 
						     <?php 
						     $query_segnalante="SELECT * FROM segnalazioni.v_segnalanti where id_segnalazione=".$id.";";
						     $result_segnalante=pg_query($conn, $query_segnalante);
								while($r_segnalante = pg_fetch_assoc($result_segnalante)) {
									echo "<br><b>Tipo</b>:".$r_segnalante['descrizione'];
									if ($r_segnalante['altro_tipo']!='') {
										echo "(".$r_segnalante['altro_tipo'].")";
									}
									echo "<br><b>Nome</b>:".$r_segnalante['nome_cognome'];
									echo "<br><b>Telefono</b>:".$r_segnalante['telefono'];
									echo "<br><b>Note segnalante</b>:".$r_segnalante['note'];
								}
						     ?>
						      <!--/div>
						      <div class="panel-footer">Panel Footer</div>
						    </div>
						  </div>
						</div-->
						
						
						<br>
						
						
						<?php
						if ($check_lav==1 OR $check_lav==-1 ){
								?>
								<hr>
								
								<div class="panel-group">
									  <div class="panel panel-warning">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#storico"><i class="fa fa-clock"></i> Storico operazioni </a>
									      </h4>
									    </div>
									    <div id="storico" class="panel-collapse collapse">
									      <div class="panel-body"-->
										<?php
										// cerco l'id_lavorazione
										$query_storico="SELECT to_char(data_ora,'DD/MM/YY HH24:MI:SS')as data_ora,log_aggiornamento";
										$query_storico= $query_storico." FROM segnalazioni.t_storico_segnalazioni_in_lavorazione WHERE id_segnalazione_in_lavorazione=".$id_lavorazione.";";
										//echo $query_storico;
										$result_storico=pg_query($conn, $query_storico);
										while($r_storico = pg_fetch_assoc($result_storico)) {
											echo "<hr>".$r_storico['data_ora'];
											echo " - " .$r_storico['log_aggiornamento'];
										}
										
							
							
										?>
									
									
									</div>
						    </div>
						  </div>
						</div>
						
						
						<?php
						
						
						
						
			
						$check_chiusura=0;  // se 0 posso chiudere se minore di 0 NO
						$check_open_ii=0; // se 0 l'utente non può aprire incarichi interni (escluso il resp della segnalazione) se 1 anche se non resp della segnalazione va bene 
						
						$check_incarichi_aperti=0; // check se incarichi ancora aperti o rifiutati
						$check_incarichi_rifiutati=0;
						$queryi="SELECT id_uo FROM segnalazioni.v_incarichi_last_update WHERE id_lavorazione=".$id_lavorazione. " and id_stato_incarico = 2;";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$check_incarichi_aperti=1; // aperti
							if ($ri['id_uo']==$periferico_inc OR $ri['id_uo']==$uo_inc ){
								$check_open_ii=1;
							}
						}
						$queryi="SELECT id_stato_incarico FROM segnalazioni.v_incarichi_last_update WHERE id_lavorazione=".$id_lavorazione. " and (id_stato_incarico = 1 OR id_stato_incarico = 4);";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$check_incarichi_rifiutati=1; // aperti
							if ($ri['id_stato_incarico']==1){
								$check_chiusura=$check_chiusura-1;
							}
							
						}
						
						
						
						$check_incarichi_interni_aperti=0; // check se incarichi interni ancora aperti o rifiutati
						$check_incarichi_interni_rifiutati=0;
						$queryi="SELECT id FROM segnalazioni.v_incarichi_interni_last_update WHERE id_lavorazione=".$id_lavorazione. " and id_stato_incarico =2;";
						//echo $queryi;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$check_incarichi_interni_aperti=1;
						}
						$queryi="SELECT id_stato_incarico FROM segnalazioni.v_incarichi_interni_last_update WHERE id_lavorazione=".$id_lavorazione. " and (id_stato_incarico = 1 OR id_stato_incarico = 4) ;";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$check_incarichi_interni_rifiutati=1;
							if ($ri['id_stato_incarico']==1){
								$check_chiusura=$check_chiusura-1;
							}
						}					
						
						
						
						
						
						// attenzione all'ordine con cui li controllo (dal maggiore al minore)
						// -1 non preso in carico
						// 1 in corso
						// 2 completato
						$check_sopralluoghi=0; // check se incarichi interni ancora aperti
						$queryi="SELECT id_stato_sopralluogo FROM segnalazioni.v_sopralluoghi_last_update WHERE id_lavorazione=".$id_lavorazione. ";";
						//$queryi="SELECT id_stato_sopralluogo FROM segnalazioni.v_sopralluoghi_last_update WHERE id_lavorazione=".$id_lavorazione. " and id_stato_sopralluogo = 3;";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							if ($ri['id_stato_sopralluogo']==3){
								$check_sopralluoghi=2;
							} else if  ($ri['id_stato_sopralluogo']==2){
								$check_sopralluoghi=1;
							} else if  ($ri['id_stato_sopralluogo']==1){
								$check_sopralluoghi=-1;
								$check_chiusura=$check_chiusura-1;
							}
						}
						/*$queryi="SELECT id FROM segnalazioni.v_sopralluoghi_last_update WHERE id_lavorazione=".$id_lavorazione. " and id_stato_sopralluogo = 2;";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$check_sopralluoghi=1;
						}						
					    $queryi="SELECT id FROM segnalazioni.v_sopralluoghi_last_update WHERE id_lavorazione=".$id_lavorazione. " and id_stato_sopralluogo = 1;";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$check_sopralluoghi=-1;
							$check_chiusura=$check_chiusura-1;
						}*/
						
						$check_provvedimenti=0; // check se incarichi interni ancora aperti
						$queryi="SELECT id FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE id_lavorazione=".$id_lavorazione. " and (id_stato_provvedimenti_cautelari = 3);";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$id_provvedimento=$ri['id'];
							$check_provvedimenti=2;
							$check_spostamento=0;
						}
						
						$queryi="SELECT id FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE id_lavorazione=".$id_lavorazione. " and (id_stato_provvedimenti_cautelari = 2);";
						//echo $query;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$id_provvedimento=$ri['id'];
							$check_provvedimenti=1;
							$check_chiusura=$check_chiusura-1;
							$check_spostamento=0;
						}
						
						$queryi="SELECT id FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE id_lavorazione=".$id_lavorazione. " and (id_stato_provvedimenti_cautelari = 1);";
						//echo $queryi;
						$resulti=pg_query($conn, $queryi);
						while($ri = pg_fetch_assoc($resulti)) {
							$id_provvedimento=$ri['id'];
							$check_provvedimenti=-1;
							$check_chiusura=$check_chiusura-1;
							$check_spostamento=0;
						}
						
						?>
					
						<div class="panel-group">
									  <div class="panel panel-info">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#list_incarichi"><i class="fa fa-briefcase"></i> Incarichi 
									        <?php
									        		if($check_incarichi_rifiutati==1) {
									        			echo	' - <i class="fa fa-exclamation fa-fw" style="color:red"></i>';
									        	  	}
													if($check_incarichi_aperti==1) {
									        			echo	' - <i class="fa fa-play" style="color:green"></i>';
									        	  	}
													
									        ?>
									        </a>
									      </h4>
									    </div>
									    <div id="list_incarichi" class="panel-collapse collapse">
									      <div class="panel-body"-->
										<?php
										// cerco l'id_lavorazione
										$query_incarichi="SELECT id, id_stato_incarico,descrizione,descrizione_stato,descrizione_uo, note_ente";
										if($check_evento_aperto==1){
											$query_incarichi= $query_incarichi." FROM segnalazioni.v_incarichi_last_update WHERE id_lavorazione=".$id_lavorazione;
										} else if($check_evento_aperto==0){
											$query_incarichi= $query_incarichi." FROM segnalazioni.v_incarichi_eventi_chiusi_last_update WHERE id_lavorazione=".$id_lavorazione;
										}
										$query_incarichi= $query_incarichi." GROUP BY id, id_stato_incarico,descrizione,descrizione_stato,descrizione_uo, note_ente;";
										
										//echo $query_incarichi;
										$result_incarichi=pg_query($conn, $query_incarichi);
										$i=0;
										while($r_incarichi = pg_fetch_assoc($result_incarichi)) {
											if ($i>0){
												echo "<hr>";
											}
											$i=$i+1;
											if ($r_incarichi['id_stato_incarico']==1){
												echo '<i class="fa fa-exclamation fa-fw" style="color:red"></i>';
											} else if ($r_incarichi['id_stato_incarico']==2){
												echo '<i class="fa fa-check" style="color:green"></i>';
											} else if ($r_incarichi['id_stato_incarico']==3){
												echo '<i class="fa fa-check-double" style="color:blue"></i>';
											} else if ($r_incarichi['id_stato_incarico']==4){
												echo '<i class="fa fa-times" style="color:orange"></i>';
											}
										
											echo $r_incarichi['descrizione'];
											echo " - " .$r_incarichi['descrizione_stato'];
											if($r_incarichi['note_ente']!=''){
												echo " (Note chiusura:" .$r_incarichi['note_ente']. ")";
											}
											echo " - " .$r_incarichi['descrizione_uo'];
											echo " - <a class=\"btn btn-info noprint\" href=\"dettagli_incarico.php?id=".$r_incarichi['id']."\"> <i class=\"fas fa-info\"></i> Dettagli</a>";
										}
										
							
									if($check_operatore==1 and $r['in_lavorazione']!='f') {
										?>
									<hr><p>
				      			<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_incarico"><i class="fas fa-plus"></i> Assegna incarico 
				      			<?php
								if($id_profilo==5 or $id_profilo==6) {
									echo " ad altro municipio";
								}
								?>
				      			</button>
						 			</p>
									<?php } ?>
									</div>
						    </div>
						  </div>
						</div>
						
						
						<div class="panel-group">
									  <div class="panel panel-info">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#list_incarichi_interni"><i class="fa fa-briefcase"></i> Incarichi interni
									        <?php
									        		if($check_incarichi_interni_rifiutati==1) {
									        			echo	' - <i class="fa fa-exclamation fa-fw" style="color:red"></i>';
									        	  	}
													if($check_incarichi_interni_aperti==1) {
									        			echo	' - <i class="fa fa-play" style="color:green"></i>';
									        	  	}
									        ?>
									        </a>
									      </h4>
									    </div>
									    <div id="list_incarichi_interni" class="panel-collapse collapse">
									      <div class="panel-body"-->
										<?php
										// cerco l'id_lavorazione
										$query_incarichi="SELECT id, id_stato_incarico,descrizione,descrizione_stato,descrizione_uo, note_ente";
										
										if($check_evento_aperto==1){
											$query_incarichi= $query_incarichi." FROM segnalazioni.v_incarichi_interni_last_update WHERE id_lavorazione=".$id_lavorazione;
										} else if($check_evento_aperto==0){
											$query_incarichi= $query_incarichi." FROM segnalazioni.v_incarichi_interni_eventi_chiusi_last_update WHERE id_lavorazione=".$id_lavorazione;
										}
										
										$query_incarichi= $query_incarichi." GROUP BY id, id_stato_incarico,descrizione,descrizione_stato,descrizione_uo, note_ente;";
										
										//echo $query_incarichi;
										$result_incarichi=pg_query($conn, $query_incarichi);
										$i=0;
										while($r_incarichi = pg_fetch_assoc($result_incarichi)) {
											if ($i>0){
												echo "<hr>";
											}
											$i=$i+1;
											if ($r_incarichi['id_stato_incarico']==1){
												echo '<i class="fa fa-exclamation fa-fw" style="color:red"></i>';
											} else if ($r_incarichi['id_stato_incarico']==2){
												echo '<i class="fa fa-check" style="color:green"></i>';
											} else if ($r_incarichi['id_stato_incarico']==3){
												echo '<i class="fa fa-check-double" style="color:blue"></i>';
											} else if ($r_incarichi['id_stato_incarico']==4){
												echo '<i class="fa fa-times" style="color:orange"></i>';
											}
										
											echo $r_incarichi['descrizione'];
											echo " - " .$r_incarichi['descrizione_stato'];
											if($r_incarichi['note_ente']!=''){
												echo " (Note chiusura:" .$r_incarichi['note_ente']. ")";
											}
											echo " - " .$r_incarichi['descrizione_uo'];
											echo " - <a class=\"btn btn-info noprint\" href=\"dettagli_incarico_interno.php?id=".$r_incarichi['id']."\"> <i class=\"fas fa-info\"></i> Dettagli</a>";
										}
										
									//echo $check_open_ii;
									if(($check_operatore==1 or $check_open_ii==1)and $r['in_lavorazione']!='f' ) {
										?>
									
									 <hr><p>
									<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_incarico_interno"><i class="fas fa-plus"></i> Assegna incarico interno </button>
						 			</p>
						 			<?php } ?>
									</div>
						    </div>
						  </div>
						</div>
						
						
						
						
						<div class="panel-group">
									  <div class="panel panel-info">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#list_sopralluoghi"><i class="fa fa-pencil-ruler"></i> Presidi
									        <?php
									        		if($check_sopralluoghi==1) {
									        			echo	' - <i class="fa fa-play" style="color:green"></i>';
									        	  	} else if($check_sopralluoghi==-1) {
									        			echo	' - <i class="fa fa-exclamation" style="color:red"></i>';
									        	  	} else if($check_sopralluoghi==2) {
									        			echo	' - <i class="fa fa-check-double" style="color:blue"></i>';
									        	  	}
									        ?>
									        </a>
									      </h4>
									    </div>
									    <div id="list_sopralluoghi" class="panel-collapse collapse">
									      <div class="panel-body"-->
										<?php
										// cerco l'id_lavorazione
										$query_sopralluoghi="SELECT id, id_stato_sopralluogo,descrizione,descrizione_stato,descrizione_uo, note_ente";
										if($check_evento_aperto==1){
											$query_sopralluoghi= $query_sopralluoghi." FROM segnalazioni.v_sopralluoghi_last_update WHERE id_lavorazione=".$id_lavorazione;
										} else if($check_evento_aperto==0){
											$query_sopralluoghi= $query_sopralluoghi." FROM segnalazioni.v_sopralluoghi_eventi_chiusi_last_update WHERE id_lavorazione=".$id_lavorazione;
										}
										$query_sopralluoghi= $query_sopralluoghi." GROUP BY id, id_stato_sopralluogo,descrizione,descrizione_stato,descrizione_uo, note_ente;";
										
										//echo $query_sopralluoghi;
										$result_sopralluoghi=pg_query($conn, $query_sopralluoghi);
										$i=0;
										while($r_sopralluoghi = pg_fetch_assoc($result_sopralluoghi)) {
											if ($i>0){
												echo "<hr>";
											}
											$i=$i+1;
											if ($r_sopralluoghi['id_stato_sopralluogo']==1){
												echo '<i class="fa fa-exclamation fa-fw" style="color:red"></i>';
											} else if ($r_sopralluoghi['id_stato_sopralluogo']==2){
												echo '<i class="fa fa-check" style="color:blue"></i>';
											} else if ($r_sopralluoghi['id_stato_sopralluogo']==3){
												echo '<i class="fa fa-check-double" style="color:green"></i>';
											} else if ($r_sopralluoghi['id_stato_sopralluogo']==4){
												echo '<i class="fa fa-times" style="color:orange"></i>';
											}
										
											echo $r_sopralluoghi['descrizione'];
											echo " - " .$r_sopralluoghi['descrizione_stato'];
											if($r_sopralluoghi['note_ente']!=''){
												echo " (Note chiusura:" .$r_sopralluoghi['note_ente']. ")";
											}
											echo " - " .$r_sopralluoghi['descrizione_uo'];
											echo " - <a class=\"btn btn-info noprint\" href=\"dettagli_sopralluogo.php?id=".$r_sopralluoghi['id']."\"> <i class=\"fas fa-info\"></i> Dettagli</a>";
										}
										
							
										if($check_operatore==1 and $r['in_lavorazione']!='f') {
										?>
									
									 <hr><p>
									<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_sopralluogo"><i class="fas fa-pencil-ruler"></i> Assegna nuovo presidio </button>
									</p>
									<?php } ?>
									</div>
						    </div>
						  </div>
						</div>
						
						
						<div class="panel-group">
									  <div class="panel panel-info">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#list_pc"><i class="fas fa-exclamation-triangle"></i> Provvedimenti cautelari
									        <?php
									        		if($check_provvedimenti==1) {
									        			echo	' - <i class="fa fa-play" style="color:green"></i>';
									        		} else if($check_provvedimenti==-1) {
									        			echo	' - <i class="fa fa-exclamation" style="color:red"></i>';
									        	  	} else if($check_provvedimenti==2) {
									        			echo	' - <i class="fa fa-check-double" style="color:blue"></i>';
									        	  	}
									        ?>
									        </a>
									      </h4>
									    </div>
									    <div id="list_pc" class="panel-collapse collapse">
									      <div class="panel-body"-->
										<?php
										// cerco l'id_lavorazione
										$query_provvedimenti="SELECT id, id_stato_provvedimenti_cautelari,descrizione,descrizione_stato,descrizione_uo, note_ente";
										if($check_evento_aperto==1){
											$query_provvedimenti= $query_provvedimenti." FROM segnalazioni.v_provvedimenti_cautelari_last_update WHERE id_lavorazione=".$id_lavorazione;
										} else if($check_evento_aperto==0){
											$query_provvedimenti= $query_provvedimenti." FROM segnalazioni.v_provvedimenti_cautelari_eventi_chiusi_last_update WHERE id_lavorazione=".$id_lavorazione;
										}
										$query_provvedimenti= $query_provvedimenti." GROUP BY id, id_stato_provvedimenti_cautelari,descrizione,descrizione_stato,descrizione_uo, note_ente;";
										
										//echo $query_sopralluoghi;
										$result_provvedimenti=pg_query($conn, $query_provvedimenti);
										$i=0;
										while($r_provvedimenti = pg_fetch_assoc($result_provvedimenti)) {
											if ($i>0){
												echo "<hr>";
											}
											$i=$i+1;
											if ($r_provvedimenti['id_stato_provvedimenti_cautelari']==1){
												echo '<i class="fa fa-exclamation fa-fw" style="color:red"></i>';
											} else if ($r_provvedimenti['id_stato_provvedimenti_cautelari']==2){
												echo '<i class="fa fa-check" style="color:blue"></i>';
											} else if ($r_provvedimenti['id_stato_provvedimenti_cautelari']==3){
												echo '<i class="fa fa-check-double" style="color:green"></i>';
											} else if ($r_provvedimenti['id_stato_provvedimenti_cautelari']==4){
												echo '<i class="fa fa-times" style="color:orange"></i>';
											}
										
											echo $r_provvedimenti['descrizione'];
											echo " - " .$r_provvedimenti['descrizione_stato'];
											if($r_provvedimenti['note_ente']!=''){
												echo " (Note chiusura:" .$r_provvedimenti['note_ente']. ")";
											}
											if($r_provvedimenti['rimosso']=='t'){
												echo ' - <i class="fa fa-times" style="color:red"></i>
												Provvedimento rimosso con successiva ordinanza sindacale';
											}
											echo " - " .$r_provvedimenti['descrizione_uo'];
											echo " - <a class=\"btn btn-info noprint\" href=\"dettagli_provvedimento_cautelare.php?id=".$r_provvedimenti['id']."\"> <i class=\"fas fa-info\"></i> Dettagli</a>";
										}
										
							
							
										?>
									<hr>Si possono aggiungere eventuali provvedimenti cautelari dalla sezione degli oggetti a rischio.
									 
									</div>
						    </div>
						  </div>
						</div>
						
						
						
						<?php
						
						include 'incarichi/panel_comunicazioni.php';
						 
						}
						if ($check_lav==1){ ?>
						<div style="text-align: center; line-height: 1.6;">
						<!--p>
				      <button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_incarico"><i class="fas fa-plus"></i> Assegna incarico </button>
						 - 
					  <button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_incarico_interno"><i class="fas fa-plus"></i> Assegna incarico interno </button>
						  - 
					  <button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_sopralluogo"><i class="fas fa-pencil-ruler"></i> Assegna sopralluogo </button>
						</p-->
						<hr>
						<?php
						if($r['id_profilo']==5) {	
							//echo "<h4><i class=\"fas fa-lock\"></i> In carico al Municipio";
							if ($check_operatore == 1){ 
								if($check_incarichi_aperti==1 OR $check_incarichi_interni_aperti==1 OR $check_sopralluoghi==1 OR $check_chiusura<0 ){
									echo "Per trasferire / chiudere la segnalazione è necessario chiudere gli incarichi attivi<br><br>";
								} else {
								?>
								<!--div style="text-align: center;"-->
									<a href="segnalazioni/trasferisci.php?l=<?php echo $r['id_lavorazione'];?>&id=<?php echo $id?>&t=3" class="btn btn-info noprint"><i class="fas fa-exchange-alt"></i> Trasferisci alla centrale PC</a> - 
								<!--/div-->
							<?php 
								}
							}
							//echo '</h4>';	
						} else if($r['id_profilo']==6) {
							//echo "<h4><i class=\"fas fa-lock\"></i> In carico al Distretto";
							if ($check_operatore == 1) { 
								if($check_incarichi_aperti==1 OR $check_incarichi_interni_aperti==1 OR $check_sopralluoghi==1 OR $check_chiusura<0 ){
									echo "Per trasferire / chiudere la segnalazione è necessario chiudere gli incarichi attivi<br><br>";
								} else {
							
								?>
									<!--div style="text-align: center;"-->										
									<a href="segnalazioni/trasferisci.php?l=<?php echo $r['id_lavorazione'];?>&id=<?php echo $id?>&t=4" class="btn btn-info noprint"><i class="fas fa-exchange-alt"></i> Trasferisci alla centrale COA</a> - 
									<!--/div-->
								<?php
								}
							}
						//echo '</h4>';
						}
						
						
						//echo $check_operatore;
						if($check_operatore==1) {
	   					echo '<button type="button" class="btn btn-danger noprint"  data-toggle="modal" ';
	   					// check sugli incarichi / sopralluoghi attivi
	   					if($check_incarichi_aperti==1 OR $check_incarichi_interni_aperti==1 OR $check_sopralluoghi==1 OR $check_chiusura<0 ){
	   						echo 'disabled="" title="Impossibile chiudere la segnalazione. Incarichi / presidi / provvedimenti cautelari risultano ancora in corso o non presi in carico."';
	   					}
	   					echo 'data-target="#chiudi"><i class="fas fa-times"></i> Chiudi segnalazione </button>';
	   				}

						?>
						
						</div>
						
						
						
						<!-- Modal incarico-->
						<div id="new_incarico" class="modal fade" role="dialog">
						  <div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Nuovo incarico</h4>
							  </div>
							  <div class="modal-body">
							  

								<form autocomplete="off" action="incarichi/nuovo_incarico.php?id=<?php echo $id_lavorazione; ?>&s=<?php echo $id; ?>" method="POST">
								<input type="hidden" name="id_profilo" id="hiddenField" value="<?php echo $profilo_sistema ?>" />
								
								<?php
								if($id_profilo==5 or $id_profilo==6) {
									if($id_profilo==5){
										$query = "select concat('com_',cod) as cod, descrizione from varie.t_incarichi_comune";
										//$query = $query ." where (cod like '%MU%' and cod not like '%00".$id_municipio."') or (cod not like '%MU%' and descrizione ilike 'distretto ".$id_municipio."')";
										$query = $query ." where (cod like '%MU%' and cod not like '%00".$id_municipio."') ";
										$query = $query ." order by descrizione;";
										//echo $query;
									} else {
										$query = "select concat('com_',cod) as cod, descrizione from varie.t_incarichi_comune";
										$query = $query ." where (cod not like '%MU%' and descrizione not like '%".$id_municipio."%') or (cod like '%MU%' and descrizione like '% ".integerToRoman($id_municipio)."%')";
										$query = $query ." order by descrizione;";
									}
								//$result = pg_query($conn, $query);
								//echo $query;

								?>
								<div class="form-group">
									  <label for="id_civico">Seleziona l'Unità Operativa cui assegnare l'incarico:</label> <font color="red">*</font>
										<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
										<option value=""> ...</option>
										<?php
										$resultr = pg_query($conn, $query);
										while($rr = pg_fetch_assoc($resultr)) {
										?>	
										<option name="id_uo" value="<?php echo $rr['cod'];?>" ><?php echo $rr['descrizione'];?></option>
										<?php } ?>
									</select>         
									 </div>
								<?php
								
									} else {
									
									?>
								<div class="form-group">
								 <label for="tipo">Tipologia di incarico:</label> <font color="red">*</font>
									<select class="form-control" name="tipo" id="tipo" onChange="getUO(this.value);"  required="">
									   <option name="tipo" value="" >  </option>
									<option name="tipo" value="direzioni" > Incarico a Direzioni (COC) </option>
									<option name="tipo" value="municipi" > Incarico a municipi </option>
									<option name="tipo" value="distretti" > Incarico a distretti di PM </option>
									<option name="tipo" value="esterni" > Incarico a Unità Operative esterne. </option>
								</select>
								</div>
									 
												 <script>
									function getUO(val) {
										$.ajax({
										type: "POST",
										url: "get_uo.php",
										data:'cod='+val,
										success: function(data){
											$("#uo-list").html(data);
										}
										});
									}

									</script>

									 
									 
									<div class="form-group">
									  <label for="id_civico">Seleziona l'Unità Operativa cui assegnare l'incarico:</label> <font color="red">*</font>
										<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
										<option value=""> ...</option>
									</select>         
									 </div>       
									<?php
									}
									
									?>
									<div class="form-group">
											 <label for="descrizione"> Descrizione operativa</label> <font color="red">*</font>
										<input type="text" name="descrizione" class="form-control" required="">
									   <small>Specificare in cosa consiste l'incarico da un punto di vista operativo</small>
									  </div>            
										  



								<button  id="conferma" type="submit" class="btn btn-primary noprint">Invia incarico</button>
									</form>

							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-default noprint" data-dismiss="modal">Annulla</button>
							  </div>
							</div>

						  </div>
						</div>
						
						
						<!-- Modal incarico interno-->
						<div id="new_incarico_interno" class="modal fade" role="dialog">
						  <div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Nuovo incarico interno</h4>
							  </div>
							  <div class="modal-body">
							  

								<form autocomplete="off" action="incarichi_interni/nuovo_incarico.php?id=<?php echo $id_lavorazione; ?>&s=<?php echo $id; ?>" method="POST">
								<input type="hidden" name="id_profilo" id="hiddenField" value="<?php echo $profilo_sistema ?>" />
								
									<?php
									$query2="SELECT id, nome FROM users.v_squadre WHERE id_stato=2 AND num_componenti > 0 AND cod_afferenza = '".$cod_profilo_squadra."' ORDER BY nome;";
									
									//echo $query2;
									$result2 = pg_query($conn, $query2);
									?>
									<div class="form-group">
									  <label for="id_civico">Seleziona squadra <?php //echo $profilo_squadre;?>:</label> <font color="red">*</font>
										<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
										<option  id="uo" name="uo" value="">Seleziona la squadra</option>
										<?php    
										while($r2 = pg_fetch_assoc($result2)) { 
											$valore=  $r2['cf']. ";".$r2['nome'];            
										?>
													
												<option id="uo" name="uo" value="<?php echo $r2['id'];?>" ><?php echo $r2['nome'].' ('.$r2['id'].')';?></option>
										 <?php } ?>
									</select>
									<small> Se non trovi una squadra adatta vai alla <a href="gestione_squadre.php" >gestione squadre</a>. </small>
									 </div>       
									 
									<div class="form-group">
											 <label for="descrizione">Descrizione operativa</label> <font color="red">*</font>
										<input type="text" name="descrizione" class="form-control" required="">
										<small>Specificare in cosa consiste l'incarico da un punto di vista operativo</small>
									  </div>            
										  



								<button  id="conferma" type="submit" class="btn btn-primary noprint">Invia incarico</button>
									</form>

							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-default noprint" data-dismiss="modal">Annulla</button>
							  </div>
							</div>

						  </div>
						</div>
						
						
						<!-- Modal sopralluogo-->
						<div id="new_sopralluogo" class="modal fade" role="dialog">
						  <div class="modal-dialog">

							<!-- Modal content-->
							<div class="modal-content">
							  <div class="modal-header">
								<button type="button" class="close" data-dismiss="modal">&times;</button>
								<h4 class="modal-title">Nuovo presidio</h4>
							  </div>
							  <div class="modal-body">
							  

								<form autocomplete="off" action="sopralluoghi/nuovo_sopralluogo.php?id=<?php echo $id_lavorazione; ?>&s=<?php echo $id; ?>" method="POST">
								<input type="hidden" name="id_profilo" id="hiddenField" value="<?php echo $profilo_sistema ?>" />
								
									<?php
									$query2= "SELECT id, nome FROM users.v_squadre WHERE id_stato=2 AND num_componenti > 0 and cod_afferenza = '".$cod_profilo_squadra."' ORDER BY nome;";
									//$query2="SELECT cf, nome FROM users.v_squadre WHERE id_stato=2 AND num_componenti > 0 and profilo = '".$profilo_squadre."' ORDER BY nome;";
									//echo $query2;
									$result2 = pg_query($conn, $query2);
									?>
									<div class="form-group">
									  <label for="id_civico">Seleziona squadra:</label> <font color="red">*</font>
										<select class="form-control" name="uo" id="uo-list" class="demoInputBox" required="">
											<option  id="uo" name="uo" value="">Seleziona la squadra</option>
											<?php    
											while($r2 = pg_fetch_assoc($result2)) { 
												$valore=  $r2['cf']. ";".$r2['nome'];            
											?>
											<option id="uo" name="uo" value="<?php echo $r2['id'];?>"><?php echo $r2['nome'].' ('.$r2['id'].')';?></option>
										 <?php } ?>
										</select>
										<small> Se non trovi una squadra adatta vai alla <a href="gestione_squadre.php" >gestione squadre</a>. </small>
									</div>       
									 
									<div class="form-group">
											 <label for="descrizione"> Descrizione</label> <font color="red">*</font>
										<input type="text" name="descrizione" class="form-control" required="">
									  </div>            
										  



								<button  id="conferma" type="submit" class="btn btn-primary noprint"  data-toggle="tooltip" data-placement="top" title="Cliccando su questo tasto confermi le informazioni precedenti e assegni il presidio alla squadra specificata">Assegna presidio</button>
									</form>

							  </div>
							  <div class="modal-footer">
								<button type="button" class="btn btn-default noprint" data-dismiss="modal">Annulla</button>
							  </div>
							</div>

						  </div>
						</div>
						
						
						<!-- Modal chiusura-->
						<div id="chiudi" class="modal fade" role="dialog">
						  <div class="modal-dialog">
						
						    <!-- Modal content-->
						    <div class="modal-content">
						      <div class="modal-header">
						        <button type="button" class="close" data-dismiss="modal">&times;</button>
						        <h4 class="modal-title">Chiudi segnalazione</h4>
						      </div>
						      <div class="modal-body">

						
						<form autocomplete="off" action="./segnalazioni/chiudi_segnalazione.php?id_lav=<?php echo $id_lavorazione;?>&id=<?php echo $r['id'];?>" method="POST">
								Proseguendo chiuderai la lavorazione di questa segnalazione e di tutte quelle unite a questa.
								<br>Non sarà più possibile assegnare incarichi, presidi o provvedimenti cautelari associati a questa segnalazione.
								<hr>
								<input type="hidden" name="descr" id="hiddenField" value="<?php echo $r['descrizione']; ?>" />
								<input type="hidden" name="crit" id="hiddenField" value="<?php echo $r['criticita']; ?>" />
						      <input type="hidden" name="idcivico" id="hiddenField" value="<?php echo $id_civico ?>" />
						      <input type="hidden" name="geom" id="hiddenField" value="<?php echo $geom ?>" />
								<div class="form-group">
								  <label for="note">Note chiusura:</label> <font color="red">*</font><br>
								  <textarea class="form-control" rows="5" id="note" name="note" required=""></textarea>
								</div>
								
								<div class="form-group">
								<label for="nome"> Segnalazione completamente risolta?</label> <font color="red">*</font><br>
								<label class="radio-inline"><input type="radio" name="risolta" value="t" required="">Sì</label>
								<label class="radio-inline"><input type="radio" name="risolta"value="f">No</label>
							</div>


							<div class="form-group">
								<label for="nome"> Pensi sia necessario inviarla automaticamente al sistema delle Manutenzioni?</label> <br>
								<label class="radio-inline"><input type="radio" name="invio" value="man">Sì</label>
								<label class="radio-inline"><input type="radio" name="invio" value="">No</label>
							</div>

								<!--div class="form-group">
								<label for="cat" class="auto-length">
									<input type="checkbox" name="cat" id="cat">
									Cliccare qua per confermare la chiusura dell'evento 
								</label>
								</div-->

								<br><br>
						
						
						
						        <button id="conferma_chiudi" type="submit" class="btn btn-danger noprint">Conferma chiusura segnalazione</button>
						            </form>
						
						      </div>
						      <div class="modal-footer">
						        <button type="button" class="btn btn-default noprint" data-dismiss="modal">Annulla</button>
						      </div>
						    </div>
						
						  </div>
						</div> 
						
						
						
						
						<hr>
						<?php 
						} 
						
						
						
						if($check_lav==0){
						// controllo se ci sono altre segnalazioni sullo stesso civico
						$check_civico=0;
						$query_civico="SELECT * FROM segnalazioni.".$table." where id_civico=".$r['id_civico']." and id !=".$id." and id_evento=".$r['id_evento']." and in_lavorazione='t';";
						//echo $query_civico . "<br>";
						$c=0;
						$result_civico=pg_query($conn, $query_civico);
								while($r_civico = pg_fetch_assoc($result_civico)) {
									$check_civico=1;
									if($c==0){
									?>
									<hr>
									<?php
									}
									$c=$c+1;
									?>
									Altre segnalazioni sullo stesso civico:
									<div class="panel-group">
									  <div class="panel panel-info">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#c_civico_s<?php echo $r_civico['id'];?>"> <?php echo $r_civico['criticita'];?></a>
									      									      <?php
									      if($r_civico['rischio'] =='t') {
												echo ' <i class="fas fa-circle fa-1x" style="color:#ff0000"></i>';
											} else if ($r_civico['rischio'] =='f') {
												echo ' <i class="fas fa-circle fa-1x" style="color:#008000"></i>';
											} else {
												echo ' <i class="fas fa-circle fa-1x" style="color:#ffd800"></i> ';
											}
											?>
									      
									      
									      </h4>
									    </div>
									    <div id="c_civico_s<?php echo $r_civico['id'];?>" class="panel-collapse collapse">
									      <div class="panel-body"-->
									<?php
										echo "<br>";
										if($r_civico['rischio'] =='t') {
											echo ' <i class="fas fa-circle fa-1x" style="color:#ff0000"></i> Persona a rischio';
										} else if ($r_civico['rischio'] =='f') {
											echo ' <i class="fas fa-circle fa-1x" style="color:#008000"></i> Non ci sono persone a rischio';
										} else {
											echo ' <i class="fas fa-circle fa-1x" style="color:#ffd800"></i> Non è specificato se ci siano persone a rischio';
										}
									?>
						
						
						
									<br><b>Data e ora inserimento</b>: <?php echo $r_civico['data_ora']; ?>
									<br><b>Descrizione</b>: <?php echo $r_civico['descrizione']; ?>
									
									<hr>
									<a class="btn btn-info noprint" href="./dettagli_segnalazione.php?id=<?php echo $r_civico['id'];?>"> <i class="fas fa-angle-right"></i> Vai alla segnalazione </a>
									<br> <br>
									<?php
									if ($r_civico['id_lavorazione']!='' and $r['id_lavorazione']=='' and ($profilo_cod_munic==$id_municipio or $profilo_cod_munic =='') and $profilo_sistema <=6 ){
										echo '<a class="btn btn-info noprint" href="./segnalazioni/unisci_segnalazione.php?id_from='.$id.'&id_to='.$r_civico['id'].'"><i class="fas fa-link"></i> Unisci segnalazione. </a>';
									} 
									?>
									</div>
						    </div>
						  </div>
						</div>
								<?php	
								}
						 if($check_civico==0 and $r['id_civico']!=''){
						 	echo "Non ci sono altre segnalazioni aperte in corrispondenza dello stesso civico.<br><br>";
						 }
						 ?>
						 
						 
						 <?php 
						// controllo se ci sono altre segnalazioni nelle vicinanze
						$check_vic=0;
						$geom_s=$r['geom'];
						$id_evento_s=$r['id_evento'];
						if ($r['id_civico']!=''){
							$query_vic="SELECT * FROM segnalazioni.".$table." where st_distance(st_transform('".$r['geom']."'::geometry(point,4326),3003),st_transform(geom,3003))< 200 and id_evento=".$r['id_evento']." and (id_civico!=".$r['id_civico']." or id_civico is null) and id !=".$id." and in_lavorazione='t';";
						} else {
							$query_vic="SELECT * FROM segnalazioni.".$table." where st_distance(st_transform('".$r['geom']."'::geometry(point,4326),3003),st_transform(geom,3003))< 200 and id_evento=".$r['id_evento']." and id !=".$id." and in_lavorazione='t';";
						}
						//echo $query_vic."<br>";
						$result_vic=pg_query($conn, $query_vic);
								while($r_vic = pg_fetch_assoc($result_vic)) {
									$check_vic=1;
									?>
									Altre segnalazioni nelle vicinanze:
									<div class="panel-group">
									  <div class="panel panel-info">
									    <div class="panel-heading">
									      <h4 class="panel-title">
									        <a data-toggle="collapse" href="#c_civico_s<?php echo $r_vic['id'];?>"> <?php echo $r_vic['criticita'].' (n. segn. '.$r_vic['id'].')';?></a>
									      <?php
									      if($r_vic['rischio'] =='t') {
												echo ' <i class="fas fa-circle fa-1x" style="color:#ff0000"></i>';
											} else if ($r_vic['rischio'] =='f') {
												echo ' <i class="fas fa-circle fa-1x" style="color:#008000"></i>';
											} else {
												echo ' <i class="fas fa-circle fa-1x" style="color:#ffd800"></i> ';
											}
											?>
									      </h4>
									    </div>
									    <div id="c_civico_s<?php echo $r_vic['id'];?>" class="panel-collapse collapse">
									      <div class="panel-body"-->
									<?php
										echo "<br>";
										if($r_vic['rischio'] =='t') {
											echo ' <i class="fas fa-circle fa-1x" style="color:#ff0000"></i> Persona a rischio';
										} else if ($r_vic['rischio'] =='f') {
											echo ' <i class="fas fa-circle fa-1x" style="color:#008000"></i> Non ci sono persone a rischio';
										} else {
											echo ' <i class="fas fa-circle fa-1x" style="color:#ffd800"></i> Non è specificato se ci siano persone a rischio';
										}
									?>
						
						
						
									<br><b>Data e ora inserimento</b>: <?php echo $r_vic['data_ora']; ?>
									<br><b>Descrizione</b>: <?php echo $r_vic['descrizione']; ?>
									
									<hr>
									<a class="btn btn-info noprint" href="./dettagli_segnalazione.php?id=<?php echo $r_vic['id'];?>"> <i class="fas fa-angle-right"></i> Vai alla segnalazione </a>
									<br> <br>
									<?php
									if ($r_vic['id_lavorazione']!='' and $r['id_lavorazione']=='' and ($profilo_cod_munic==$id_municipio or $profilo_cod_munic =='') and $profilo_sistema <= 6){
										echo '<a class="btn btn-info noprint" href="./segnalazioni/unisci_segnalazione.php?id_from='.$id.'&id_to='.$r_vic['id'].'"><i class="fas fa-link"></i> Unisci segnalazione. </a>';
									} 
									?>
									
									
										</div>
							    </div>
							  </div>
							</div>
								<?php	
								}
								
						 if($check_vic==0){
						 	echo "Non ci sono altre segnalazioni aperte nelle vicinanze.<br><br>";
						 }
						 ?>
						
						<hr>
						<div style="text-align: center;">
						<?php
						//echo "test";
						//echo $profilo_sistema;
						//echo $id_lavorazione;
						 if ($id_lavorazione=='' and ($profilo_cod_munic==$id_municipio or $profilo_cod_munic =='') and $profilo_sistema <= 6){ ?>
								<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#lavorazione"> <i class="fas fa-plus"></i> 
								<?php
								 // solo se non ancora in lavorazione
								 
									if ($check_civico==0 and $check_vic==0){
										echo "Elabora segnalazione";
									} else {
										echo "Elabora come nuova segnalazione<br><small>dopo aver verificato che non si possa unire</small>";
									}
								 
								
								?>
								</button>	
						<?php }	else {
							echo 'Il tuo profilo non può prendere in carico la segnalazione. Solo la Protezione Civile e il Municipio '.$id_municipio.' possono prendere in carico la segnalazione.';
						}?>
						</div>




<!-- Modal lavorazione-->
<div id="lavorazione" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Inizia ad elaborare la segnalazione</h4>
      </div>
      <div class="modal-body">
      

        <form autocomplete="off" action="./segnalazioni/import_lavorazione.php" method="POST">
        
         <input type="hidden" name="id" id="hiddenField" value="<?php echo $id ?>" />
         
			<div class="form-group">
					<label for="nome"> Chi si occuperà della gestione della segnalazione ?</label> <font color="red">*</font><br>
					<?php 
					//echo $profilo_sistema;
					if ($profilo_sistema <= 3) { ?>
						<label class="radio-inline"><input type="radio" name="uo" required="" value="3" checked="checked" >Prendi in carico come centrale PC </label>
					<?php } else if ($profilo_sistema == 4) { ?>
						<label class="radio-inline"><input type="radio" name="uo" required="" value="4" checked="checked" >Prendi in carico come centrale COA </label>
					<?php } else if ($profilo_sistema == 5) { ?>
						<label class="radio-inline"><input type="radio" name="uo" required="" value="3">Invia alla centrale PC </label>
						<input type="hidden" name="mun" id="hiddenField" value="<?php echo 'on'; ?>" />
						<label class="radio-inline"><input type="radio" name="uo" required="" value="5">Elabora come Municipio  </label>
					<?php } else if ($profilo_sistema == 6) { ?>
						<label class="radio-inline"><input type="radio" name="uo" required="" value="4">Invia alla centrale COA </label>
						<label class="radio-inline"><input type="radio" name="uo" required="" value="6">Elabora come Distretto PM </label>
					<?php } ?>
				</div>
		
			<hr>
			
        <button  id="conferma" type="submit" class="btn btn-primary noprint" >Inserisci in lavorazione</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default noprint" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>   
<hr>
						
<?php } ?>
						
						
						<br>
						</div> 
						<div class="col-md-6">
						<h4> <i class="fas fa-map-marker-alt"></i> Indirizzo </h4>
						
						<?php
						require('./indirizzo_embedded.php');
						//echo $lon;
						$zoom=16;
						?>
						<hr>
						<h4> <div id="break"> <i class="fas fa-map-marked-alt"></i> Mappa </div> 
						<?php
						//cerco se ci sono elementi a rischio:
						$query_er='SELECT id_segnalazione FROM segnalazioni.join_oggetto_rischio 
						WHERE id_segnalazione ='.$id.';';
						$result_er=pg_query($conn, $query_er);
						while($r_er = pg_fetch_assoc($result_er)) {
							//$check_spostamento=0;
							// questo per ora non è vero..
						}
						
						
						if ($check_spostamento==1 and $check_operatore==1) {
								$zoom_plus=$zoom+1;
								echo ' - <a href="sposta_segnalazione.php?id='.$id.'&lat='.$lat.'&lon='.$lon.'&z='.$zoom_plus.'" class="btn btn-info noprint">
								<i class="fas fa-map-marker-alt"></i> Sposta segnalazione</a>';
						} else {
							echo "E' possibile spostare le segnalazioni di cui si è responsabile a
							meno che: <ul>
							<li> non ci siano elementi a rischio / provvedimenti cautelari associati </li>
							<li> non ci siano altre segnalazioni congiunte nelle vicinanze </li></ul>";
						}
						?>
						</h4>
						<!--div id="map_dettaglio" style="width: 100%; padding-top: 100%;"></div-->
						<div id="map" style="width: 100%; padding-top: 100%;">
						</div>
						
						<?php
						$querys="select  to_char(data_ora_spostamento, 'HH24:MI'::text) AS ora, 
						to_char(data_ora_spostamento, 'DD/MM/YYYY'::text) AS data 
						from segnalazioni.t_spostamento_segnalazioni 
						WHERE id_segnalazione=".$id.";"; 
						
						$conts=0;
						$results=pg_query($conn, $querys);
						while($rs = pg_fetch_assoc($results)) {
							if($conts==0) {
								echo "<h3><i class=\"fas fa-arrows-alt\"></i> Spostamento segnalazioni</h3>";
								echo "<ul>";
							}
							$conts=$conts+1;
							echo "<li>Segnalazione spostata alle ore ".$rs["ora"]." del ".$rs["data"]." </li>";
						}
						if($conts>0) {
							echo "</ul>";
						}
						?>
						
						
						
						<!--div style="width: 100%; padding-top: 100%;"-->
							<!--iframe class="embed-responsive-item" style="width:100%; padding-top:0%; height:600px;" src="./mappa_leaflet.php#16/<?php echo $lat;?>/<?php echo $lon;?>"></iframe-->
						<!--/div-->
						
						
						
						<hr>
						<div id="er">
						<?php
						require('./req_bottom.php');
						   

						   $id_segnalazione=$id;
						   
							include './segnalazioni/section_oggetto_rischio.php';

							include './mappa_leaflet_embedded.php';
							
							
							
							
							// cerco l'oggetto a rischio
							/*$check_or=0;
							$query_or="SELECT * FROM segnalazioni.join_oggetto_rischio WHERE id_segnalazione=".$id." AND attivo='t';";
							$result_or=pg_query($conn, $query_or);
							while($r_or = pg_fetch_assoc($result_or)) {
								$check_or=1;
								$id_tipo_oggetto_rischio=$r_or['id_tipo_oggetto'];
								$id_oggetto_rischio=$r_or['id_oggetto'];
							}
							//echo $query_or;
							// cerco i dettagli dell'oggetto a rischio
							$query_or2="SELECT * from segnalazioni.tipo_oggetti_rischio where id= ".$id_tipo_oggetto_rischio.";";
							//echo $query_or2;
							$result_or2=pg_query($conn, $query_or2);
							while($r_or2 = pg_fetch_assoc($result_or2)) {
								$nome_tabella_oggetto_rischio=$r_or2['nome_tabella'];
								$descrizione_oggetto_rischio=$r_or2['descrizione'];
								$nome_campo_id_oggetto_rischio=$r_or2['campo_identificativo'];
							}
							if($check_or==1) {
								echo "<h4> Oggetto a rischio </h4>";
								echo "<b>Tipo oggetto a rischio</b>:".$descrizione_oggetto_rischio;
								echo "<br><b>Id oggetto a rischio </b>:".$id_oggetto_rischio;
							} else if ($check_or==0) {
								echo "<h4> Nessun oggetto a rischio segnalato.</h4>";
							}*/
							// eventualmente da tirare fuori altri dettagli
							//$query_or3="SELECT * from ".$nome_tabella_oggetto_rischio."  where ".$nome_campo_id_oggetto_rischio." = ".$id_oggetto_rischio.";";
							
 						?>	
 						</div>
						</div>
						
            <div class="row noprint">
			<hr>
					<div style="text-align: center;">
					<button class="btn btn-info noprint" onclick="printDiv('page-wrapper')">
               <i class="fa fa-print" aria-hidden="true"></i> Crea stampa o PDF 
			   <i class="fa fa-file-pdf" aria-hidden="true"></i></button>
               </div>            
            </div>
			<br><br>
			
					<?php
					}
					?>

				
            </div>
            <br>
            <!-- /.row -->
            
            <br>
    </div>
    <!-- /#wrapper -->

<?php 




require('./footer.php');
?>



</body>

</html>
