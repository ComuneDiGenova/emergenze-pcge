<?php 
$check_index=0; 
require ('./note_ambiente.php');
if(isset($_GET['r'])){
	if ($_GET['r']=='true'){
		require('./check_evento.php');
	}
} else {
	if(basename($_SERVER['PHP_SELF']) == 'index.php') {
		$check_index=1;
	}

}
if(isset($_GET['i'])){
	if ($_GET['i']=='true'){
		$check_index=1;
	}
}
if (isset($subtitle)) {
	$subtitle=$subtitle;
} else {
	$subtitle=str_replace('_',' ',$_GET['s']);
}
?>

<style>
.dropdown-menu > li > a {
  white-space: normal;
}

.dropdown-menu{
   max-height:600px;
   overflow-y: scroll;
}
</style>



<!-- Navigation -->
<div id="navbar_emergenze">
        <nav class="navbar navbar-default navbar-fixed-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <!--a class="navbar-brand" href="index.php"> </a-->
                <div class="navbar-brand"> <i class="fas fa-server"></i>
                Gestione emergenze <?php echo $note_ambiente?> - <?php echo $subtitle?> 
                <?php if ($profilo_sistema==10){ echo '- <font color="#007c37"> Op. num verde </font>';}?> 
                </div>
            </div>
            <!-- /.navbar-header -->
            <ul class="nav navbar-top-links navbar-right">
             
			<?php
				if($check_index == 1) {
			?>
				<li class="nav-item active">
					<a class="nav-link" title="Elenco segnalazioni" href="#segn_sintesi"><i class="fas fa-list"></i></a>
				</li>
				<li class="nav-item active">
					<a class="nav-link" title="Mappa segnalazioni" href="#mappa_segnalazioni"><i class="fas fa-map-marked-alt"></i></a>
				</li>
				<li class="nav-item active">
					<a class="nav-link" title="Elenco presidi mobili" href="#presidi_mobili"><i class="fas fa-ambulance"></i></a>
				</li>
			<?php
			} else {
				if ($profilo_sistema == 10){
			?>	

				<li class="nav-item active">
					<a class="nav-link" title="Torna alla prima pagina" href="index.php"><i class="fas fa-tachometer-alt fa-fw"></i> Dashboard</a>
				</li>
			<?php
			}
			}
			if ($profilo_sistema == 8 and $check_reperibilita==1){
			?>
			
			<li class="nav-item active">
            <a href="reperibilita_aziende.php"> 
            <i class="fas fa-user-clock">
            </i> Reperibilita' </a>
        </li>
			<?php
			}
			?>	 
				
				
				<?php
				
				$len_c=count($eventi_attivi_c);
					if($check_evento==0 and $len_c ==0) {
				?>		

                <li class="dropdown">
                    <a class="dropdown-toggle"  data-toggle="tooltip" data-placement="bottom" title="Visualizza dettagli allerte ed eventi"  href="#">
                        <i class="fas fa-circle fa-fw"></i> <i class="fas fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-messages">
                        <li>
                            <a href="#">
                                <div>
                                    <strong>Nessun evento in corso</strong>
                                    <span class="pull-right text-muted">
                                        <em>...</em>
                                    </span>
                                </div>
                                <div>Nessun evento in corso</div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="nuovo_evento.php">
                                <div>
                                    <strong>Crea nuovo evento</strong>
                                    <span class="pull-right text-muted">
                                        <em>Link</em>
                                    </span>
                                </div>
                                <div>Vai alla pagina di creazione eventi.</div>
                            </a>
                        </li>
                        <li class="divider"></li>
                        
                    </ul> </li>
                    <!-- /.dropdown-messages -->

				<?php		
					} else {
				?>
                <li class="dropdown">

                    <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                    		<i class="fas fa-chevron-circle-down fa-fw"></i> <i class="fas fa-caret-down"></i>
							
							<?php
							//echo $len_c;
								if ($len_c > 0 and $profilo_ok <=3){
							?>
								<i class="fas fa-hourglass-end faa-ring animated" title="ATTENZIONE: ci sono <?php echo $len_c;?> eventi in chiusura" style="color:red"></i>
							<?php		
								}
							?>
							<?php
							//echo $check_pausa;
								if ($check_pausa >=1){
							?>
								<i class="fas fa-pause" title="Ci sono <?php echo $check_pausa;?> eventi sospesi" style="color:orange"></i>
							<?php		
								}
							?>
							
                    		<i class="fas fa-circle fa-1x" title="<?php echo $descrizione_allerta; ?>" style="color:<?php echo $color_allerta; ?>"></i> 
                    		<i class="fas fa-circle fa-1x" title="<?php echo $descrizione_foc; ?>" style="color:<?php echo $color_foc; ?>"></i>
                    		<i class="fas fa-phone-square fa-1x" style="color:<?php echo $color_nverde; ?>"></i>
                    </a>
                     
                    <ul class="dropdown-menu dropdown-messages">
                        <li>
                            <a href="dettagli_evento.php?e=<?php echo $id_evento; ?>">
                                <div>
                                <?php if( $descrizione_allerta!= 'Nessuna allerta') {?>
                                    <strong> Allerta <?php echo $descrizione_allerta; ?> in corso</strong>
                                 <?php } else { ?>
                                 	<strong> Nessuna allerta in corso</strong>
                                 <?php }  ?> 
                                    <span class="pull-right text-muted">
                                        <em><i class="fas fa-circle fa-1x" style="color:<?php echo $color_allerta; ?>"></i></em>
                                    </span>
                                </div>
                                <div> Clicca per visualizzare i dettagli sugli eventi in corso. </div>
                            </a>
                        </li>

                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <?php if( $descrizione_foc!= '-') {?>
                                    <strong> Fase di <?php echo $descrizione_foc; ?> in corso</strong>
                                 <?php } else { ?>
                                 	<strong> Nessuna Fase Operativa in corso</strong>
                                 <?php }  ?> 
                                    <span class="pull-right text-muted">
                                        <em><i class="fas fa-circle fa-1x" style="color:<?php echo $color_foc; ?>"></i></em>
                                    </span>
                                </div>
                                <!--div> Clicca per visualizzare tutte le Fasi Operative Comunali in corso, previste o passate.</div-->
                            </a>
                        </li>
                        
								<li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                    <?php if( $contatore_nverde > 0) {?>
                                    <strong> Numero verde attivo</strong>
                                 <?php } else { ?>
                                 	<strong> Numero verde non attivo</strong>
                                 <?php }  ?> 
                                    <span class="pull-right text-muted">
                                        <em><i class="fas fa-phone-square  fa-1x" style="color:<?php echo $color_nverde; ?>"></i></em>
                                    </span>
                                </div>
                                <!--div> Clicca per visualizzare le attivazioni numero verde in corso, previste o passate.</div-->
                            </a>
                        </li>
                        <li class="divider"></li>
                        <li>
                                <a href="#">
                                <div>
                                
                                <?php 
                                $len=count($eventi_attivi);	               
		               				if($len==1) {   
	               				?>
                                    <strong>Evento in corso</strong>
								<?php } else if ($len==0) { ?>
                                 	<strong>Nessun evento in corso</strong>
                                <?php } else {
                                 	?>
                                 	<strong>Eventi in corso (<?php echo $len;?>)</strong>
                                <?php
								
                                 	}
                                ?>
                                 	
                                    <span class="pull-right text-muted">
                                        <em><i class="fas fa-play"></i></em>
                                    </span>
                                </div> 
                                </a>
                                <?php 
                                for ($i=0;$i<$len;$i++){
                                ?>
                                <a href="#">
                                <b><i>Tipo</i>: <?php echo $tipo_eventi_attivi[$i][1];?></b>
                                
								<?php 
								if ($sospeso[$i]==1){
								?>
									<span class="pull-right text-muted">
                                        <em><i class="fas fa-pause faa-ring animated" style="color:orange" 
										title="Evento temporaneamente sospeso fino alle <?php echo $sospensione[$i];?>"></i></em>
                                    </span>
								<?php 
								}
								?>
                                </a><!--br--><a href="dettagli_evento.php">
                                   - Visualizza dettagli <br>
                                 </a>
                                  <a href="monitoraggio_meteo.php?id=<?php echo $tipo_eventi_attivi[$i][0];?>">
                                   - Vai al monitoraggio meteo <br>
                                 </a>
                                 <a href="reportistica.php?id=<?php echo $tipo_eventi_attivi[$i][0];?>">
                                   - Vai alla pagina dei report <br>
                                 </a>
                                 
                                <?php
                                }
                                ?>
                                
                            
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="#">
                                <div>
                                <?php 
                                	               
		               				if($len_c==1) {   
	               				   ?>
                                    <strong>Evento in chiusura</strong>
                                 <?php } else if ($len_c==0) { ?>
                                 	<strong>Nessun evento in fase di chiusura</strong>
                                 <?php } else {
                                 	?>
                                 	<strong>Eventi in chiusura (<?php echo $len_c;?>)</strong>
                                 	<?php
                                 	}
                                 	?>
                                 	
                                    <span class="pull-right text-muted">
                                        <em><i class="fas fa-hourglass-end"></i></em>
                                    </span>
                                </div>
                                <?php 
                                for ($i=0;$i<$len_c;$i++){
                                ?>
								<a href="#">
                                <b><i>Tipo</i>: <?php echo $tipo_eventi_c[$i][1];?></b>
								
								<?php 
								if ($sospeso_c[$i]==1){
								?>
									<span class="pull-right text-muted">
                                        <em><i class="fas fa-pause faa-ring animated" style="color:orange" 
										title="Evento temporaneamente sospeso fino alle <?php echo $sospensione_c[$i];?>"></i></em>
                                    </span>
								<?php 
								}
								?><!--br-->
                                </a>
                                 <a href="dettagli_evento.php">
                                   - Visualizza dettagli <br>
                                 </a>
                                  <a href="monitoraggio_meteo.php?id=<?php echo $tipo_eventi_c[$i][0];?>">
                                   - Vai al monitoraggio meteo <br>
                                 </a>
                                 <a href="reportistica.php?id=<?php echo $tipo_eventi_c[$i][0];?>">
                                   - Vai alla pagina dei report <br>
                                 </a>
                                <?php
                                }
                                ?>
                                
                            </a>
                        </li>
                        
                        
                        
                        
                        
                        <!--li class="divider"></li>
                        <li>
                            <a href="dettagli_evento.php">
                                <div>
                                    <strong>Dettagli</strong>
                                    <span class="pull-right text-muted">
                                        <em>Link</em>
                                    </span>
                                </div>
                                <div>Vai alla pagina con i dettagli degli eventi in corso per visualizzare e gestire anche tutte le allerte.</div>
                            </a>
                        </li>
                        <li class="divider"></li-->
                    </ul> </li>
                    <!-- /.dropdown-messages -->

				
				
				<?php		
					}
				?>


				
				<style>
				.fa-stack[data-count]:after{
				  position:absolute;
				  right:10%;
				  top:10%;
				  content: attr(data-count);
				  font-size:60%;
				  padding:.6em;
				  border-radius:999px;
				  line-height:.75em;
				  color: white;
				  background:rgba(255,0,0,.85);
				  text-align:center;
				  min-width:2em;
				  font-weight:bold;
				}
				</style>
				<?php if($segn_limbo>0 and $profilo_ok < 7){?>
					
					<li id="limbo" class="dropdown">
                    <!--a class="dropdown-toggle fa-stack fa-1x has-badge" data-count="4" data-toggle="dropdown" href="#"-->
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"-->
						<i class="fa fa-exclamation fa-fw faa-ring animated" title="Segnalazioni ancora da elaborare" style="color:red"></i> <i class="fas fa-caret-down"></i>
                    </a>	
                    <ul class="dropdown-menu dropdown-alerts">
                    
                    <li>
					<a href="#">
                                <div>
                                    <i class="fas fa-user-shield"></i> Segnalazioni da elaborare
                                    <span class="pull-right text-muted small"><?php echo $segn_limbo;?></span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $segn_limbo; $ii++) {
									echo "<br><a href=\"dettagli_segnalazione.php?id=".$id_segn_limbo[$ii]."\">";
									echo '<i class="fas fa-exclamation" title="da elaborare" style="color:#ff0000"></i>';
									echo " Vai alla segnalazione ". $id_segn_limbo[$ii] ." " ;
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                    <li class="divider"></li>
                    
                    
                    
					<li>
						<a href="index.php#segn_limbo_table">
							<div>
								Visualizza elenco segnalazioni da elaborare!
							</div>
						</a>
					</li>
					</ul>
					</li>
					
				<?php }?>
				
				
				
				<?php if($segn_limbo_municipi>0 and $profilo_ok =3){?>
					
					<li id="limbo" class="dropdown">
                    <!--a class="dropdown-toggle fa-stack fa-1x has-badge" data-count="4" data-toggle="dropdown" href="#"-->
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"-->
						<i class="fas fa-hashtag fa-fw faa-ring animated" 
						title="Segnalazioni provenienti dai municipi" style="color:red"></i> <i class="fas fa-caret-down"></i>
                    </a>	
                    <ul class="dropdown-menu dropdown-alerts">
                    
                    <li>
					<a href="#">
                                <div>
                                    <i class="fas fa-user-shield"></i> Segnalazioni provenienti dai municipi
                                    <span class="pull-right text-muted small"><?php echo $segn_limbo_municipi;?></span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $segn_limbo_municipi; $ii++) {
									echo "<br><a href=\"dettagli_segnalazione.php?id=".$id_segn_limbo_municipi[$ii]."\">";
									echo '<i class="fas fa-hashtag" title="da elaborare" style="color:#ff0000"></i>';
									echo " Vai alla segnalazione ". $id_segn_limbo_municipi[$ii] ." " ;
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                    <li class="divider"></li>
                    
                    
                    
					<li>
						<a href="index.php#segn_limbo2_table">
							<div>
								Visualizza elenco segnalazioni provenienti dai municipi!
							</div>
						</a>
					</li>
					</ul>
					</li>
					
				<?php }?>
				

				
                <li id="notifiche_profilo" title="Incarichi in corso" class="dropdown" >
                    <!--a class="dropdown-toggle fa-stack fa-1x has-badge" data-count="4" data-toggle="dropdown" href="#"-->
					<a class="dropdown-toggle" data-toggle="dropdown" href="#"-->
					<?php if ($count_resp >0) { ?>
						<i class="fas fa-bell fa-fw faa-ring animated" style="color:#ff0000"></i> <?php echo $count_resp;?> <i class="fas fa-caret-down"></i>
					<?php } else { ?>	
                        <i class="fas fa-bell fa-fw"></i>  <i class="fas fa-caret-down"></i>
					<?php } ?>	
                    </a>	
                    <ul class="dropdown-menu dropdown-alerts" style="white-space: normal;">
					<li>
						<a href="#">
							<div>
								Notifiche <?php echo $descrizione_profilo;?>
							</div>
						</a>
					</li>
					<li class="divider"></li>
					<li>
					<a href="#">
                                <div>
                                    <i class="fas fa-user-shield"></i> Incarichi
                                    <span class="pull-right text-muted small"><?php echo $i_assegnati_resp;?></span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $i_assegnati_resp; $ii++) {
									echo "<br><a href=\"dettagli_incarico.php?id=".$id_i_assegnati_resp[$ii]."\">";
									if ($stato_i_assegnati_resp[$ii]==2){
										echo '<i class="fas fa-play" title="in lavorazione" style="color:#5cb85c"></i>';
									} else {
										echo '<i class="fas fa-exclamation" title="da elaborare" style="color:#ff0000"></i>';
									}
									echo " Descrizione: ".$descrizione_i_assegnati_resp[$ii]."" ;
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_incarichi c
									join segnalazioni.v_incarichi_last_update s ON s.id = c.id
									where id_destinatario='".$profilo_ok."' and c.id =  ".$id_i_assegnati_resp[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                        <li class="divider"></li>
					<li>
					<a href="#">
                                <div>
                                    <i class="fas fa-user-tag"></i> Incarichi interni
                                    <span class="pull-right text-muted small"><?php echo $ii_assegnati_resp;?></span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $ii_assegnati_resp; $ii++) {
									echo "<br><a href=\"dettagli_incarico_interno.php?id=".$id_ii_assegnati_resp[$ii]."\">";
									if ($stato_ii_assegnati_resp[$ii]==2){
										echo '<i class="fas fa-play" title="in lavorazione" style="color:#5cb85c"></i>';
									} else {
										echo '<i class="fas fa-exclamation" title="da elaborare" style="color:#ff0000"></i>';
									}
									echo " Descrizione: ".$descrizione_ii_assegnati_resp[$ii]."" ;
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_incarichi_interni c
									join segnalazioni.v_incarichi_interni_last_update s ON s.id = c.id
									where id_destinatario='".$profilo_ok."' and c.id =  ".$id_ii_assegnati_resp[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                        <li class="divider"></li>
					<li>
					<a href="#">
                                <div>
                                    <i class="fas fa-pencil-ruler"></i> Presidi fissi
                                    <span class="pull-right text-muted small"><?php echo $s_assegnati_resp;?> </span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $s_assegnati_resp; $ii++) {
									echo "<br><a href=\"dettagli_sopralluogo.php?id=".$id_s_assegnati_resp[$ii]."\">";
									if ($stato_s_assegnati_resp[$ii]==2){
										echo '<i class="fas fa-play" title="in lavorazione" style="color:#5cb85c"></i>';
										$query_cs='SELECT * FROM segnalazioni.t_sopralluoghi_richiesta_cambi 
										WHERE id_sopralluogo ='.$id_s_assegnati_resp[$ii].' AND (eseguito = \'f\' OR eseguito is null);';
										$result_cs = pg_query($conn, $query_cs);
										while($r_cs = pg_fetch_assoc($result_cs)) {
											 echo '(<i class="fas fa-exchange-alt" title="richiesta cambio squadra" style="color:#ff0000"></i>)';
										}
									} else {
										echo '<i class="fas fa-exclamation" title="da elaborare" style="color:#ff0000"></i>';
									}
									echo " Descrizione: ".$descrizione_s_assegnati_resp[$ii]."" ;
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_sopralluoghi c
									join segnalazioni.v_sopralluoghi_last_update s ON s.id = c.id
									where id_destinatario='".$profilo_ok."' and c.id =  ".$id_s_assegnati_resp[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                        
                        <li class="divider"></li>
                        
                        <li>
					<a href="#">
                                <div>
                                    <i class="fas fa-pencil-ruler"></i> Presidi mobili
                                    <span class="pull-right text-muted small"><?php echo $sm_assegnati_resp;?> </span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $sm_assegnati_resp; $ii++) {
									echo "<br><a href=\"dettagli_sopralluogo_mobile.php?id=".$id_sm_assegnati_resp[$ii]."\">";
									if ($stato_sm_assegnati_resp[$ii]==2){
										echo '<i class="fas fa-play" title="in lavorazione" style="color:#5cb85c"></i>';
										$query_cs='SELECT * FROM segnalazioni.t_sopralluoghi_mobili_richiesta_cambi 
										WHERE id_sopralluogo ='.$id_sm_assegnati_resp[$ii].' AND (eseguito = \'f\' OR eseguito is null);';
										//echo $query_cs;
										$result_cs = pg_query($conn, $query_cs);
										while($r_cs = pg_fetch_assoc($result_cs)) {
											 echo '(<i class="fas fa-exchange-alt" title="richiesta cambio squadra" style="color:#ff0000"></i>)';
										}
									} else {
										echo '<i class="fas fa-exclamation" title="da elaborare" style="color:#ff0000"></i>';
									}
									echo " Descrizione: ".$descrizione_sm_assegnati_resp[$ii]." " ;
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_sopralluoghi_mobili c
									join segnalazioni.v_sopralluoghi_mobili_last_update s ON s.id = c.id
									where id_destinatario='".$profilo_ok."' and c.id =  ".$id_sm_assegnati_resp[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                        
                        
                        <li class="divider"></li>
                    <li>
					<a href="#">
                                <div>
                                    <i class="fas fa-exclamation-triangle"></i> Provv. cautelari
                                    <span class="pull-right text-muted small"><?php echo $pc_assegnati_resp;?> </span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $pc_assegnati_resp; $ii++) {
									echo "<br><a href=\"dettagli_provvedimento_cautelare.php?id=".$id_pc_assegnati_resp[$ii]."\">";
									if ($stato_pc_assegnati_resp[$ii]==2){
										echo '<i class="fas fa-play" title="in lavorazione" style="color:#5cb85c"></i>';
									} else {
										echo '<i class="fas fa-exclamation" title="da elaborare" style="color:#ff0000"></i>';
									}
									echo " Tipo: ".$tipo_pc_assegnati_resp[$ii];
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_provvedimenti_cautelari c
									join segnalazioni.v_provvedimenti_cautelari_last_update s ON s.id = c.id
									where id_destinatario='".$profilo_ok."' and c.id =  ".$id_pc_assegnati_resp[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>" ;
								}
								?>
                            
							</a>
                        </li>
						<li class="divider"></li>
                        <li>
                            <a class="text-center" href="update_notifiche.php?id=<?php echo $operatore; ?>">
                                <strong>Segna i messaggi come letti</strong>
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                        
						<li class="divider"></li>
                        <li>
                            <a class="text-center" href="index.php#panel-notifiche">
                                <strong>Vedi tutti i conteggi</strong>
                                <i class="fas fa-angle-right"></i>
                            </a>
                        </li>
                    </ul>
                </li>
                
				
				<li id="notifiche_squadra" class="dropdown">
                    <a class="dropdown-toggle" title="Notifiche squadra" data-toggle="dropdown" href="#">
                        <?php if ($count_squadra >0) { ?>
						<i class="fas fa-users" style="color:#ff0000"></i> <?php echo $count_squadra;?> <i class="fas fa-caret-down"></i>
					<?php } else { ?>	
                        <i class="fas fa-users"></i>  <i class="fas fa-caret-down"></i>
					<?php } ?>	
                    </a>
					<?php if (isset($nome_squadra_operatore)){ ?>	
                    <ul class="dropdown-menu dropdown-alerts">
					<li>
						<a href="#">
							<div>
								Notifiche squadra <?php echo $nome_squadra_operatore;?>
							</div>
						</a>
					</li>

					<?php $id_squadra_operatore2='sq_'.$id_squadra_operatore; ?>
                        <li class="divider"></li>
					<li>
					<a href="#">
                                <div>
                                    <i class="fas fa-user-tag"></i> Incarichi interni
                                    <span class="pull-right text-muted small"><?php echo $ii_assegnati_squadra;?></span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $ii_assegnati_squadra; $ii++) {
									echo "<br><a href=\"dettagli_incarico_interno.php?id=".$id_ii_assegnati_squadra[$ii]."\">Vai ai dettagli";
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_incarichi_interni c
									join segnalazioni.v_incarichi_interni_last_update s ON s.id = c.id
									where id_destinatario='".$id_squadra_operatore2."' and c.id =  ".$id_ii_assegnati_squadra[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                        <li class="divider"></li>
					<li>
					<a href="#">
                                <div>
                                    <i class="fas fa-pencil-ruler"></i> Presidi
                                    <span class="pull-right text-muted small"><?php echo $s_assegnati_squadra;?></span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $s_assegnati_squadra; $ii++) {
									echo "<br><a href=\"dettagli_sopralluogo.php?id=".$id_s_assegnati_squadra[$ii]."\">Visualizza dettagli" ;
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_sopralluoghi c
									join segnalazioni.v_sopralluoghi_last_update s ON s.id = c.id
									where id_destinatario='".$id_squadra_operatore2."' and c.id =  ".$id_s_assegnati_squadra[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                        <li class="divider"></li>
                    <li>
					<a href="#">
                                <div>
                                    <i class="fas fa-exclamation-triangle"></i> Presidi mobili
                                    <span class="pull-right text-muted small"><?php echo $sm_assegnati_squadra;?></span>
								</div>                              
                                <?php
								for ($ii = 0; $ii < $sm_assegnati_squadra; $ii++) {
									echo "<br><a href=\"dettagli_provvedimento_cautelare.php?id=".$id_sm_assegnati_squadra[$ii]."\">Visualizza dettagli" ;
									$query_m="SELECT c.data_ora_stato FROM segnalazioni.v_comunicazioni_sopralluoghi_mobili c
									join segnalazioni.v_sopralluoghi_mobili_last_update s ON s.id = c.id
									where id_destinatario='".$id_squadra_operatore2."' and c.id =  ".$id_sm_assegnati_squadra[$ii]."
									and to_timestamp(c.data_ora_stato, 'DD/MM/YYYY HH24:MI:SS'::text) > 
									case 
									when (select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."') is null
									then '2001-01-01'
									else 
									(select data_ora from users.utenti_message_update where matricola_cf = '".$operatore."')
									end;";
									//echo $query_m;
									$mess_not_readed=0;
									$result_m = pg_query($conn, $query_m);
									while($r_m = pg_fetch_assoc($result_m)) {
										$mess_not_readed=$mess_not_readed+1;
									}
									if ($mess_not_readed > 0) {
										echo '<br><i class="fas fa-envelope faa-ring animated" style="color:#ff0000"></i> Ci potrebbero essere nuovi messaggi da leggere';
									}
									echo "</a>";
								}
								?>
                            
							</a>
                        </li>
                    </ul>
				<?php }?>
                </li>

				
				
                <!-- /.dropdown -->
                <li class="dropdown">
					<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<?php if ($check_esterno_update==1){ ;?>
                        <i class="fas fa-user fa-fw faa-ring animated" style="color:#ff0000"></i> <i class="fas fa-caret-down"></i>
				<?php } else { ?>
						<i class="fas fa-user fa-fw"></i> <i class="fas fa-caret-down"></i>
				<?php }?>
					</a>
					
                    <ul class="dropdown-menu dropdown-user">
						<li><a href="./profilo.php"><i class="fas fa-user fa-fw"></i> CF: <?php echo $CF;?> 
						(Clicca per visualizzare i dettagli e gestire le notifiche telegram <i class="fab fa-telegram" style="color:#0088CC" ></i>)</a>
                        </li>
                        <!--li><a href="./profilo.php"><i class="fas fa-user fa-fw"></i> User Profile</a>
                        </li-->
                        <!--li><a href="#"><i class="fas fa-gear fa-fw"></i> Settings (DEMO)</a>
                        </li-->
                        <li class="divider"></li>
                        <li><a href="../../Shibboleth.sso/Logout"><i class="fas fa-sign-out fa-fw"></i>Logout</a>
                        </li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
				<li>
				<a target="_guida_in_linea"  title="Guida in linea" href="https://manuale-sistema-di-gestione-emergenze-comune-di-genova.readthedocs.io/it/latest/"> 
                            <i class="fas fa-question"></i></a>
				</li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->
            
 </nav>           
</div>
