<?php



?>

<!-- riga iniziale con i contatori -->
<div class="row">
    <!-- EVENTI IN CORSO -->
<div class="col-lg-3 col-md-6">
        <div class="panel panel-info">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-tasks fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?php echo $contatore_eventi; ?></div>
                        <div> <?php echo $preview_eventi; ?></div>
                    </div>
                </div>
            </div>
            <!--a href="./dettagli_evento.php"-->
                <div class="panel-footer">
                    <!--span class="pull-left">
                    Clicca su "gestione eventi" per visualizzare i dettagli</span-->
                    <?php
                                if ($check_evento==1){
                                            $len=count($eventi_attivi);	               
                                    for ($i=0;$i<$len;$i++){
                                    ?><li>					                                  
                                            <a href="dettagli_evento.php?e=<?php echo $eventi_attivi[$i];?>">
                                            Dettagli evento 
                                            - Id=<?php echo $eventi_attivi[$i];?>
                                            </a>
                                    </li>
                                    <?php
                                    }
                                }
                                ?>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            <!--/a-->
        </div>
    </div>



    <!-- ALLERTE -->
<div class="col-lg-3 col-md-6">
        <div class="panel panel-allerta">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-exclamation-triangle fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge"><?php echo $contatore_allerte; ?></div>
                        <div><?php echo $preview_allerte; ?>!</div>
                    </div>
                </div>
            </div>
            <a href="./dettagli_evento.php">
                <div class="panel-footer">
                    <span class="pull-left">Aggiungi/modifica allerte</span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>



    <div class="col-lg-3 col-md-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-map-marked-alt  fa-5x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div class="huge">
                        <?php
                            echo $segn_tot;
                        ?>
                        
                        </div>
                        <div>Segnalazioni pervenute</div>
                    </div>
                </div>
            </div>
            <a href="elenco_segnalazioni.php">
                <div class="panel-footer">
                    <span class="pull-left">Elenco segnalazioni</span>
                    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                    <div class="clearfix"></div>
                </div>
            </a>
        </div>
    </div>
    
    
    
    <div class="col-lg-3 col-md-6">
        <div class="panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-cogs fa-5x"></i>
                    </div>
        <div class="col-xs-9 text-right">
            <div class="huge">
            <?php
                echo $segn_lav;
            ?>
            
            </div>
            <div>Segnalazioni in lavorazione</div>
        </div>
    </div>
</div>
<a href="mappa_segnalazioni.php">
    <div class="panel-footer">
    <span class="pull-left">Vedi su mappa</span>
    <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
    <div class="clearfix"></div>
</div>
</a>
</div>
</div>






</div>
<!-- /.row -->