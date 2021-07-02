<?php 
# sidebar definition
?>

<div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">


                        <!--li class="sidebar-search">
                            <div class="input-group custom-search-form">
                                <input type="text" class="form-control" placeholder="Search...">
                                <span class="input-group-btn">
                                <button class="btn btn-default" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </span>
                            </div>
                            <!-- /input-group -->
                        </li-->
                        <?php
							if(basename($_SERVER['PHP_SELF']) == 'index.php') {
						?>
							<li class="nav-item active">
								<a class="nav-link" href="#segn_sintesi"><i class="fas fa-list"></i> Lista segnalazioni</a>
							</li>
							<li class="nav-item active">
								<a class="nav-link" href="#mappa_segnalazioni"><i class="fas fa-map-marked-alt"></i> Mappa segnalazioni</a>
							</li>
						<?php
						} else {
						?>
							
							<li class="nav-item active">
								<a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt fa-fw"></i> Dashboard</a>
							</li>
						<?php
						}
						if (in_array($profilo_sistema, array(10,11))==false){
						?>

                        <li>
                            <a href="#"><i class="fa fa-stream fa-fw"></i> Gestione eventi<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                            	  <li>
                            	  <?php if ($profilo_ok==3){ ?>
                                    <a href="nuovo_evento.php"><i class="fas fa-plus"></i> Crea nuovo evento</a>
                                <?php } ?>
                                </li>
                                
                                
                                <li>
                                    <a href="#"><i class="fas fa-info"></i> Dettagli eventi in corso</a>
                                </li>
                                <ul class="nav nav-third-level">
                                

                                <?php
                                if ($check_evento==1){
                                            $len=count($eventi_attivi);	               
                                    for ($i=0;$i<$len;$i++){
                                    ?><li>					                                  
                                            <a href="dettagli_evento.php?e=<?php echo $eventi_attivi[$i];?>">
                                            <i class="fas fa-chart-line"></i> Dettagli evento 
                                            - Id=<?php echo $eventi_attivi[$i];?>
                                            </a>
                                    </li>
                                    <?php
                                    }
                                }
                                ?>

                                </ul>
                                <li>
                                    <a href="#"><i class="fas fa-info"></i> Dettagli eventi in chiusura</a>
                                </li>
                                <ul class="nav nav-third-level">
                                

                                <?php
                                if ($check_evento==1){
                                            $len=count($eventi_attivi_c);	               
                                    for ($i=0;$i<$len;$i++){
                                    ?><li>					                                  
                                            <a href="dettagli_evento_c.php?e=<?php echo $eventi_attivi_c[$i];?>">
                                            <i class="fas fa-chart-line"></i> Dettagli evento 
                                            - Id=<?php echo $eventi_attivi_c[$i];?>
                                            </a>
                                    </li>
                                    <?php
                                    }
                                }
                                ?>

                                </ul>
                                <?php if ($profilo_sistema<=6){ ?>
                                 <li>
                                    <a href="lista_eventi.php"><i class="fas fa-list"></i> Lista eventi / reportistica </a>
                                </li> 
                                <?php } ?>
								<?php if ($profilo_ok==3){ ?>
                                <li>
                                   <a href="attivita_sala_emergenze.php"><i class="fas fa-sitemap"></i> Assegna turni sala emergenze</a>
                                </li> 
                                <li>
                                   <a href="storico_sala_emergenze.php"><i class="fas fa-history"></i> Storico turni sala emergenze</a> 
                                </li> 			
								<?php } ?>			
								<li>					                                  
  											<a href="bollettini_meteo.php"><i class="fas fa-list"></i> Lista bollettini</a>
                                </li>
								
								
								<!--li>
									<a href="mappa_meteo.php">
									<i class="fas fa-map"></i>
									Mappa meteo</a>
								</li-->
								<!--li-->
								<!--a href="rete_idro.php"-->
								<!--a href="http://omirl.regione.liguria.it/Omirl/#/map" target="_blank">
								<i class="fas fa-tint"></i> Rete meteorologica regionale (OMIRL) </a>
								</li>
								<li>
                                    <a href="rassegna_stampa.php"><i class="far fa-newspaper"></i> Rassegna stampa comunale </a>
                                </li-->
								
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
						
						<?php 
						}
						//****************************************************
						// profili particolari  NUMERO VERDE
						if ($profilo_sistema!=10){ ?>
						<li>
                            <a href="#"><i class="fas fa-cloud-sun-rain"></i> Monitoraggio <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
								<li>					                                  
  									<a href="mire.php"><i class="fas fa-search"></i> Mire su corsi d'acqua</a>
                                </li>
								<li>					                                  
  									<a href="idrometri_arpa.php"><i class="fas fa-chart-line"></i> Grafici idrometri </a>
                                </li>
                        <?php
               			if ($check_evento==1){
									$len=count($eventi_attivi);	               
	               			for ($i=0;$i<$len;$i++){
	               			?><li>					                                  
  									<a href="monitoraggio_meteo.php?id=<?php echo $eventi_attivi[$i];?>">
  									<i class="fas fa-chart-line"></i> Monitoraggio meteo <br>
  									<small>
  									(Tipo <?php echo $tipo_eventi_attivi[$i][1];?> - Id=<?php echo $eventi_attivi[$i];?>)</small>
  									</a>
                        	</li>
                        	<?php
	               			}
	               	}
	               	?>
						    </ul>
                            <!-- /.nav-second-level -->
                        </li>
						
						<?php
						}
						if (in_array($profilo_sistema, array(10,11))==false){
						?>
						
						
                        <li>
                            <a href="gestione_squadre.php"><i class="fa fa-users"></i> Gestione squadre</a>
                            
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-user-clock"></i> Registro presenze<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                                <li>
                                    <a href="elenco_presenti.php"><i class="fas fa-user-check"></i> Utenti presenti</a>
                                </li>
                                <li>
                                    <a href="elenco_presenti_storico.php"><i class="fas fa-user-times"></i> Storico utenti presenti</a>
                                </li>
                            </ul>
                        </li>
 								<?php 
 								}
								//****************************************************
								// profili particolari  NUMERO VERDE
 								if ($profilo_sistema==10){ ?>
 								
									<li>
									   <a href="nuova_richiesta.php">
									   <i class="fas fa-plus"></i>
									   Registra segnalazione /richiesta</a>
								   </li>
						   
								   <li>
									   <a href="elenco_richieste.php"> 
									   <i class="fas fa-list-ul">
									   </i> Elenco richieste
										<br> <small>(<i class="fas fa-play"></i>eventi in corso / <i class="fas fa-hourglass-half"></i> in chiusura)</small></a>
								   </li>
								   <li>
									   <a href="elenco_segnalazioni.php">Elenco delle segnalazioni 
									   <br><small> (<i class="fas fa-play"></i>eventi in corso / <i class="fas fa-hourglass-half"></i> in chiusura)</small>
									   </a>
								   </li>
 								
 								<?php } 
								//****************************************************
								// profili particolari  MONITORAGGIO METEO
 								if ($profilo_sistema==11){ ?>
								<li>
								<a href="#"><i class="fa fa-stream fa-fw"></i> Gestione eventi<span class="fa arrow"></span></a>
								<ul class="nav nav-second-level">
									  
									<li>
										<a href="dettagli_evento.php"><i class="fas fa-info"></i> Dettagli eventi in corso</a>
									</li>
									<li>
										<a href="dettagli_evento_c.php"><i class="fas fa-hourglass-end faa-ring animated"></i> Dettagli eventi in fase di chiusura</a>
									</li>
									<li>
										<a href="lista_eventi.php"><i class="fas fa-list"></i> Lista eventi</a>
									</li> 
									<li>					                                  
												<a href="bollettini_meteo.php"><i class="fas fa-list"></i> Lista bollettini</a>
									</li>
								</ul>
								<!-- /.nav-second-level -->
								</li>
								<li>
									<a href="lista_mail_meteo.php">
									<i class="fas fa-at"></i>
									Contatti a cui inviare aggiornamento meteo</a>
								</li>
 								<?php }
 						
						
						
						
						
						if ($profilo_sistema<9){ ?>
 						                 
                        
                         <li>
                            <a href="#"><i class="fas fa-map-marked-alt"></i> Segnalazioni <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                            
                            <?php if ($profilo_ok==3){ ?>
                            	<li>
                                    <a href="nuova_richiesta.php">
                                    <i class="fas fa-plus"></i>
                                    Registra segnalazione /richiesta</a>
                                </li>
                             <?php } else if ($profilo_sistema < 9) { ?>
                            		<li>
                                    <a href="nuova_segnalazione.php">Nuova segnalazione</a>
                                </li>
                             <?php }?>
                                
                               
                                <li>
                                    <a href="elenco_segnalazioni.php">Elenco delle segnalazioni 
                                    <br><small> (<i class="fas fa-play"></i>eventi in corso / <i class="fas fa-hourglass-half"></i> in chiusura)</small>
                                    </a>
                                </li>
                                <li>
                                    <a href="elenco_segnalazioni_ev_chiusi.php">Elenco storico delle segnalazioni
                                    <br><small> (<i class="fas fa-stop"></i>eventi chiusi)</small>
                                    </a>
                                </li>
                                 <li>
                                    <a href="elenco_richieste.php"> 
                                    <i class="fas fa-list-ul">
                                    </i> Elenco richieste generiche
                                     <br> <small>(<i class="fas fa-play"></i>eventi in corso / <i class="fas fa-hourglass-half"></i> in chiusura)</small></a>
                                </li>

                                <li>
                                    <a href="elenco_richieste_storico.php">
                                    <i class="fas fa-list"></i>
                                    Elenco richieste generiche
                                    <br> <small>Eventi passati</small></a>
                                </li>
											<li>
                                    <a href="elenco_inc.php">Elenco incarichi 
                                    <small> (<i class="fas fa-play"></i> in corso)</small>
                                    </a>
                                </li>
                                <li>
                                    <a href="elenco_inc_ev_chiusi.php">Elenco incarichi 
                                    <small> (<i class="fas fa-stop"></i> chiusi)</small>
                                    </a>
                                </li>
                                <?php if ($profilo_sistema==1 OR $profilo_sistema==8){ ?>
                                <li>
                                    <a href="elenco_storico_mail.php"> 
                                    <i class="fas fa-envelope-open-text"></i>Elenco mail incarichi 
                                    <small><br>(Utenti esterni)</small>
                                    </a>
                                </li>
								<?php } ?>
                                <li>
                                    <a href="elenco_inc_int.php">Elenco incarichi interni
                                    <small> (<i class="fas fa-play"></i> in corso)</small>
                                    </a>
                                </li>
                                <li>
                                    <a href="elenco_inc_int_ev_chiusi.php">Elenco incarichi interni 
                                    <small> (<i class="fas fa-stop"></i> chiusi)</small>
                                    </a>
                                </li>
								<li>
                                    <a href="elenco_comunicazioni.php"> <i class="fas fa-comments"></i>Elenco comunicazioni 

                                    </a>
                                </li>
                                <?php if ($profilo_sistema<3){ ?>
                                <li>
                                    <a href="elenco_comunicazioni_riservate.php"> <i class="fas fa-user-secret"></i><i class="fas fa-comments"></i>Elenco comunicazioni riservate

                                    </a>
                                </li>
								<?php } ?>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
						
                        <li>
                            <a href="#"><i class="fas fa-pencil-ruler"></i> Presidi <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                            <?php if ($profilo_ok==3 or ($profilo_sistema==8 and $uo_inc=='uo_1')){ ?>
                            		<li>
                                    <a href="nuovo_sopralluogo.php">Nuovo presidio fisso</a>
                                </li>
                                <li>
                                    <a href="nuovo_sopralluogo_mobile.php">Nuovo presidio mobile</a>
                                </li>
                            <?php } ?>
                                <li>
                                    <a href="elenco_sopralluoghi.php">Elenco presidi fissi attivi</a>
                                </li>
                                <li>
                                    <a href="elenco_sopralluoghi_mobili.php">Elenco presidi mobili attivi</a>
                                </li>
								<li>
                                    <a href="elenco_sopralluoghi_ev_chiusi.php">Elenco presidi fissi chiusi</a>
                                </li>
                                <li>
                                    <a href="elenco_sopralluoghi_mobili_ev_chiusi.php">Elenco presidi mobili chiusi</a>
                                </li>
                                
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        <li>
                            <a href="#"><i class="fas fa-exclamation-triangle"></i> Provvedimenti cautelari <span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                            <?php if ($profilo_ok==3){ ?>
                            		<li>
                                    <a href="nuovo_pc_sgombero.php">Sgombero civici</a>
                                </li>
								<li>
                                    <a href="nuovo_pc_sottopasso.php">Interdizione accesso sottopassi</a>
                                </li>
								<li>
                                    <a href="nuovo_pc_strada.php">Chiusura strada</a>
                                </li>
                        <?php } ?>
								<li>
                                    <a href="elenco_pc.php">Elenco provvedimenti cautelari (eventi aperti)</a>
                                </li>
								<li>
                                    <a href="elenco_pc_ev_chiusi.php">Elenco provvedimenti cautelari (eventi chiusi)</a>
                                </li>

                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        

                         <li>
                            <a href="#"><i class="fa fa-address-book fa-fw"></i> Gestione utenti<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
								 		<li>
                                    <a href="elenco_utenti.php">
									<i class="fas fa-user-clock">
									</i>Elenco utenti sistema</a>
                                </li>
                            	<li>
                                    <a href="add_volontario.php">
                                    <i class="fas fa-user-plus"></i>
                                    Aggiunta utenti esterni</a>
                                </li>
                                <?php if($profilo_sistema<=3 or $profilo_sistema==8){ ?>
                                <li>
                                    <a href="reperibilita_aziende.php"> 
                                    <i class="fas fa-user-clock">
                                    </i> Reperibilit&agrave COC Esterni</a>
                                </li>
								<?php } ?>
								
								 <?php if ($profilo_sistema<=6){ ?>
                                 
                                <li>
                                    <a href="lista_dipendenti.php">
                                    <i class="fas fa-user-tie"></i>
                                    Elenco dipendenti 
									<?php if($profilo_sistema==1){ ?>
									<small> (modifica permessi)</small>
									<?php } ?>
									</a>
                                </li>
                                <?php } ?>
                                <li>
                                    <a href="lista_volontari.php">
                                    <i class="fas fa-user-lock"></i>
                                    Elenco utenti esterni
									<?php if($profilo_sistema==1){ ?>
									<small> (modifica permessi)</small>
									<?php } ?></a>
                                </li>
                                <li>
                                    <a href="lista_mail.php">
                                    <i class="fas fa-at"></i>
                                    Contatti a cui notificare incarichi</a>
                                </li>
								
								<?php if($profilo_sistema==1 or $profilo_sistema==2){ ?>
								<li>
                                    <a href="lista_mail_meteo.php">
                                    <i class="fas fa-at"></i>
                                    Contatti a cui inviare aggiornamento meteo</a>
                                </li>
								<?php } ?>
                                <li>
                                    <!--a href="rubrica.php"-->
									<a href="http://172.19.48.7/rubrica.php" target="_blank">
                                    <i class="fas fa-address-book"></i>
                                    Rubrica Comune di Genova <small>(rete intranet)</small>
									</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                        
                        <?php } ?>
                        
                        
						
                        <!--li>
                            <a href="reportistica.php"> <i class="fas fa-chart-pie"></i> Riepilogo e report</a>
                        </li-->
                         <!--li>
                            <a target="_guida_in_linea" href="https://manuale-sistema-di-gestione-emergenze-comune-di-genova.readthedocs.io/it/latest/"> 
                            <i class="fas fa-question"></i> Guida in linea</a>
                        </li-->
                        <?php if ($profilo_sistema==1){ ?>
                        <li>
                            <a href="#"><i class="fa fa-user-shield"></i> Funzionalità amministratore sistema<span class="fa arrow"></span></a>
                            <ul class="nav nav-second-level">
                            		<li>
                                    <a href="elenco_amm.php"><i class="fas fa-edit"></i> Modifica tabelle decodifica</a>
                                </li>
                                <li>
                                    <a href="conteggi.php"><i class="fas fa-chart-pie"></i> 
                                    Contatori</a>
                                </li>
                                <li>
                                    <a href="log_update.php"><i class="fas fa-clipboard-list"></i> Log update GeoDataBase</a>
                                </li>
                            </ul>
                            <!-- /.nav-second-level -->
                        </li>
                       <?php } ?>
                        
                    </ul>
                    
                    <div style="text-align: center;">
                    
                    <!--div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-traffic-light fa-fw"></i> Mappa ufficiale <a target="_new" href="http://www.allertaliguria.gov.it">allertaliguria</a> 
                        </div>
                        <div class="panel-body"-->
                         <a target="_new" title="Vai al sito https://allertaliguria.regione.liguria.it/" href="https://allertaliguria.regione.liguria.it/">   
							  <img class="nav nav-second-level" imageborder="0" alt="Problema di visualizzazione immagine causato da sito https://allertaliguria.regione.liguria.it/" 
							  width="98%" src="https://allertaliguria.regione.liguria.it/mappa_allerta_render.php">
                        </a>
						<!--/div>                    
                    </div-->
                  
						
						<hr>
						   <a title="Vai al sito rete OMIRL"  href="http://omirl.regione.liguria.it/Omirl/#/map" target="_blank">
                		<img class="nav nav-second-level" src="../img/omirl.png" width="50%" alt="">
                		</a>
                		<hr>
                		<img class="nav nav-second-level" src="../img/pc_ge.png" width="65%" alt="">
                		<br>
                		
							<a href="http://www.ponmetro.it/" target="_blank">
                		<img class="nav nav-second-level" src="../img/pon_metro/Logo_PONMetro-1.png" width="50%" alt="">
                		</a>
                	</div>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>

