<?php
$subtitle = "Dashboard o pagina iniziale";
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto">

    <title>Gestione emergenze</title>
    <?php
    require("./req.php");
    require(explode("emergenze-pcge", getcwd())[0] . "emergenze-pcge/conn.php");
    require("./check_event_fake.php");
    require("../conn.php");

    // require("./check_evento.php");
    require("./conteggi_dashboard.php");


    if ($profilo_sistema == 10) {
        header("location: ./index_nverde.php");
    }
    ?>

</head>

<body data-spy="scroll" data-target=".navbar">

    <div id="wrapper">

        <div id="navbar1">
            <?php
            require("navbar_up.php");
            ?>
        </div>

        <?php
        require("./navbar_left.php")
            ?>


        <div id="page-wrapper">
            <!--div class="row"-->
            <!--div class="col-lg-12">
                    <h1 class="page-header">Dashboard</h1>
                </div-->
            <!-- /.col-lg-12 -->
            <!--/div-->
            <!-- /.row -->






            <style type="text/css">
                .panel-allerta {
                    border-color: <?php echo $color_allerta;
                    ?>;
                }

                .panel-allerta>.panel-heading {
                    border-color: <?php echo $color_allerta;
                    ?>;
                    color: white;
                    background-color: <?php echo $color_allerta;
                    ?>;
                }

                .panel-allerta>a {
                    color: <?php echo $color_allerta;
                    ?>;
                }

                .panel-allerta>a:hover {
                    color: #337ab7;
                    /* <?php echo $color_allerta; ?>;*/
                }

                .bootstrap-table .table>thead>tr>th {
                    vertical-align: center;
                }
            </style>


            <br><br>
            <div class="row">
                <div class="col-lg-12">

                    <div id="segn_sintesi">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-list fa-fw"></i> Sintesi segnalazioni da elaborare
                                <!--div class="pull-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                        Altro
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu pull-right" role="menu">
                                        <li class="divider"></li>
                                        <li><a href="elenco_segnalazioni.php">Vai all'elenco completo delle segnalazioni</a>
                                        </li>
                                    </ul>
                                </div-->
                                <div class="pull-right">
                                    <div class="btn-group">
                                        <a class="btn btn-default btn-xs" href="elenco_segnalazioni.php">
                                            <i class="fas fa-list"></i> Elenco segnalazioni</a>

                                    </div>
                                </div>

                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div>
                                            <section id="segn_limbo_table">
                                                <table id="segnalazioni_limbo" class="table table-condensed"
                                                    style="word-break:break-all; word-wrap:break-word;"
                                                    data-toggle="table"
                                                    data-url="./tables/griglia_segnalazioni_limbo.php"
                                                    data-show-export="false" data-search="true"
                                                    data-click-to-select="true" data-pagination="true"
                                                    data-sidePagination="true" data-show-refresh="true"
                                                    data-show-toggle="false" data-show-columns="true"
                                                    data-toolbar="#toolbar">




                                                    <thead>

                                                        <tr>
                                                            <!--th data-field="state" data-checkbox="true"></th-->
                                                            <!--th data-field="in_lavorazione" data-sortable="false" data-formatter="nameFormatter" data-visible="true" ></th-->
                                                            <th data-field="id" data-sortable="false"
                                                                data-formatter="nameFormatterEditL" data-visible="true">
                                                            </th>
                                                            <th data-field="id_evento" data-sortable="true"
                                                                data-visible="true">Evento</th>
                                                            <th data-field="rischio" data-sortable="true"
                                                                data-formatter="nameFormatterRischio"
                                                                data-visible="true">Persone<br>a rischio</th>
                                                            <th style="word-break:break-all; word-wrap:break-word;"
                                                                data-field="criticita" data-sortable="true"
                                                                data-visible="true">Tipo criticità</th>
                                                            <th data-field="nome_munic" data-sortable="true"
                                                                data-visible="true">Mun.</th>
                                                            <th data-field="localizzazione" data-sortable="false"
                                                                data-visible="true">Civico</th>
                                                            <!--th data-field="id_evento" data-sortable="true"  data-visible="true">Id<br>evento</th-->
                                                            <!--th style="word-break:break-all; word-wrap:break-word;" data-field="tipo_evento" data-sortable="true"  data-visible="true">Tipo evento</th-->
                                                            <th style="word-break:break-all; word-wrap:break-word;"
                                                                data-field="data_ora" data-sortable="true"
                                                                data-visible="true">Data e ora</th>
                                                            <th style="word-break:break-all; word-wrap:break-word;"
                                                                data-field="descrizione" data-sortable="true"
                                                                data-visible="true">Descrizione</th>
                                                            <!--th data-field="note" data-sortable="false" data-visible="true" >Note</th-->


                                                        </tr>
                                                    </thead>
                                                    <script>




                                                        function nameFormatterEditL(value) {

                                                            return '<a class="btn btn-warning" title="Visualizza dettagli" href=./dettagli_segnalazione.php?id=' + value + '>' + value + '<!--i class="fas fa-edit"></i--></a>';

                                                        }

                                                        function nameFormatterRischio(value) {
                                                            //return '<i class="fas fa-'+ value +'"></i>' ;

                                                            if (value == 't') {
                                                                return '<i class="fas fa-exclamation-triangle" style="color:#ff0000"></i>';
                                                            } else if (value == 'f') {
                                                                return '<i class="fas fa-check" style="color:#5cb85c"></i>';
                                                            }
                                                            else {
                                                                return '<i class="fas fa-question" style="color:#505050"></i>';
                                                            }
                                                        }


                                                    </script>

                                                </table>

                                            </section>

                                        </div>
                                        <!-- /.table-responsive -->
                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                    </div>


                    <div id="segn_sintesi2">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-list fa-fw"></i> Sintesi segnalazioni aperte
                                <!--div class="pull-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                        Altro
                                        <span class="caret"></span>
                                    </button>
                                    <ul class="dropdown-menu pull-right" role="menu">
                                        <li class="divider"></li>
                                        <li><a href="elenco_segnalazioni.php">Vai all'elenco completo delle segnalazioni</a>
                                        </li>
                                    </ul>
                                </div>
                            </div-->
                                <div class="pull-right">
                                    <div class="btn-group">
                                        <a class="btn btn-default btn-xs" href="elenco_segnalazioni.php">
                                            <i class="fas fa-list"></i> Elenco segnalazioni</a>

                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div>


                                            <?php
                                            //if ($profilo_ok==3){
                                            ?>

                                            <section id="segn_limbo2_table">
                                                <!--hr-->
                                                <h4>Segnalazioni prese in carico provenienti dai municipi</h4>
                                                <table id="segnalazioni" class="table table-condensed"
                                                    style="vertical-align: middle;" data-toggle="table"
                                                    data-url="./tables/griglia_segnalazioni_mun_pp.php"
                                                    data-show-export="false" data-search="true"
                                                    data-click-to-select="true" data-pagination="true"
                                                    data-sidePagination="true" data-show-refresh="true"
                                                    data-show-toggle="false" data-show-columns="true"
                                                    data-toolbar="#toolbar">




                                                    <thead>

                                                        <tr>
                                                            <!--th data-field="state" data-checkbox="true"></th-->
                                                            <th data-field="id" style="vertical-align:center"
                                                                data-sortable="false" data-formatter="nameFormatterEdit"
                                                                data-visible="true"></th>
                                                            <!--th data-field="id" data-sortable="false" data-formatter="nameFormatterMappa1" data-visible="true" ></th-->
                                                            <th data-field="id_evento" data-sortable="true"
                                                                data-visible="true">Evento</th>
                                                            <th data-field="in_lavorazione" data-sortable="true"
                                                                data-halign="center" data-valign="center"
                                                                data-formatter="nameFormatter" data-visible="true">Stato
                                                            </th>
                                                            <!--th data-field="rischio" data-sortable="true" data-formatter="nameFormatterRischio" data-visible="true">Persone<br>a rischio</th-->
                                                            <th data-field="criticita" data-sortable="true"
                                                                data-visible="true">Tipo<br>criticità</th>
                                                            <!--th data-field="data_ora" data-sortable="true"  data-visible="true">Data e ora</th-->
                                                            <!--th data-field="descrizione" data-sortable="true"  data-visible="true">Descrizione</th-->
                                                            <th data-field="nome_munic" data-sortable="true"
                                                                data-visible="true">Mun.</th>
                                                            <th data-field="localizzazione" data-sortable="false"
                                                                data-visible="true">Civico</th>
                                                            <th data-field="incarichi" data-sortable="false"
                                                                data-halign="center" data-valign="center"
                                                                data-formatter="nameFormatterIncarichi"
                                                                data-visible="true">Incarichi<br>in corso</th>
                                                            <th data-field="num" data-sortable="false"
                                                                data-visible="true">Num<br>segn</th>
                                                            <!--th data-field="note" data-sortable="false" data-visible="true" >Note</th>
            <th data-field="id_evento" data-sortable="true"  data-visible="true">Id<br>evento</th>
            <th data-field="tipo_evento" data-sortable="true"  data-visible="true">Tipo<br>evento</th-->

                                                        </tr>
                                                    </thead>
                                                    <script>

                                                        function nameFormatter(value) {
                                                            if (value == 't') {
                                                                return '<i class="fas fa-play" title="in lavorazione" style="color:#5cb85c"></i>';
                                                            } else if (value == 'f') {
                                                                return '<i class="fas title="chiusa" fa-stop"></i>';
                                                            } else {
                                                                return '<i class="fas fa-exclamation" title="da eleaborare" style="color:#ff0000"></i>';
                                                            }

                                                        }

                                                        function nameFormatterIncarichi(value) {
                                                            if (value == 't') {
                                                                return '<div style="text-align: center;"><i class="fas fa-circle" title="incarichi in corso" style="color:#f2d921"></i></div>';
                                                            } else if (value == 'f') {
                                                                return '<div style="text-align: center;"><i class="fas fa-circle" title="nessun incarico in corso" style="color:#ff0000"></i></div>';
                                                            }
                                                        }

                                                        function nameFormatterEdit(value) {

                                                            return '<a class="btn btn-warning btn-sm" title="Vai ai dettagli" href=./dettagli_segnalazione.php?id=' + value + '>' + value + '<!--i class="fas fa-edit"></i--></a>';

                                                        }

                                                        function nameFormatterMappa1(value, row) {
                                                            //var test_id= row.id;
                                                            return ' <button type="button" class="btn btn-info btn-sm" title="anteprima mappa" data-toggle="modal" data-target="#myMap' + value + '"><i class="fas fa-map-marked-alt"></i></button> \
    <div class="modal fade" id="myMap'+ value + '" role="dialog"> \
    <div class="modal-dialog"> \
      <div class="modal-content">\
        <div class="modal-header">\
          <button type="button" class="close" data-dismiss="modal">&times;</button>\
          <h4 class="modal-title">Anteprima segnalazione '+ value + '</h4>\
        </div>\
        <div class="modal-body">\
        <iframe class="embed-responsive-item" style="width:100%; padding-top:0%; height:600px;" src="./mappa_leaflet.php#17/'+ row.lat + '/' + row.lon + '"></iframe>\
        </div>\
        <!--div class="modal-footer">\
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
        </div-->\
      </div>\
    </div>\
  </div>\
</div>';
                                                        }
                                                    </script>

                                                </table>

                                            </section>
                                            <?php
                                            //}
                                            ?>

                                            <hr>
                                            <h4>Segnalazioni prese in carico direttamente</h4>
                                            <table id="segnalazioni" class="table table-condensed"
                                                style="vertical-align: middle;" data-toggle="table"
                                                data-url="./tables/griglia_segnalazioni_pp.php" data-show-export="false"
                                                data-search="true" data-click-to-select="true" data-pagination="true"
                                                data-sidePagination="true" data-show-refresh="true"
                                                data-show-toggle="false" data-show-columns="true"
                                                data-toolbar="#toolbar">




                                                <thead>

                                                    <tr>
                                                        <!--th data-field="state" data-checkbox="true"></th-->
                                                        <th data-field="id" style="vertical-align:center"
                                                            data-sortable="false" data-formatter="nameFormatterEdit"
                                                            data-visible="true"></th>
                                                        <!--th data-field="id" data-sortable="false" data-formatter="nameFormatterMappa1" data-visible="true" ></th-->
                                                        <th data-field="id_evento" data-sortable="true"
                                                            data-visible="true">Evento</th>
                                                        <th data-field="in_lavorazione" data-sortable="true"
                                                            data-halign="center" data-valign="center"
                                                            data-formatter="nameFormatter" data-visible="true">Stato
                                                        </th>
                                                        <!--th data-field="rischio" data-sortable="true" data-formatter="nameFormatterRischio" data-visible="true">Persone<br>a rischio</th-->
                                                        <th data-field="criticita" data-sortable="true"
                                                            data-visible="true">Tipo<br>criticità</th>
                                                        <!--th data-field="data_ora" data-sortable="true"  data-visible="true">Data e ora</th-->
                                                        <!--th data-field="descrizione" data-sortable="true"  data-visible="true">Descrizione</th-->
                                                        <th data-field="nome_munic" data-sortable="true"
                                                            data-visible="true">Mun.</th>
                                                        <th data-field="localizzazione" data-sortable="false"
                                                            data-visible="true">Civico</th>
                                                        <th data-field="incarichi" data-sortable="false"
                                                            data-halign="center" data-valign="center"
                                                            data-formatter="nameFormatterIncarichi" data-visible="true">
                                                            Incarichi<br>in corso</th>
                                                        <th data-field="num" data-sortable="false" data-visible="true">
                                                            Num<br>segn</th>
                                                        <!--th data-field="note" data-sortable="false" data-visible="true" >Note</th>
            <th data-field="id_evento" data-sortable="true"  data-visible="true">Id<br>evento</th>
            <th data-field="tipo_evento" data-sortable="true"  data-visible="true">Tipo<br>evento</th-->

                                                    </tr>
                                                </thead>
                                                <script>

                                                    function nameFormatter(value) {
                                                        if (value == 't') {
                                                            return '<i class="fas fa-play" title="in lavorazione" style="color:#5cb85c"></i>';
                                                        } else if (value == 'f') {
                                                            return '<i class="fas title="chiusa" fa-stop"></i>';
                                                        } else {
                                                            return '<i class="fas fa-exclamation" title="da eleaborare" style="color:#ff0000"></i>';
                                                        }

                                                    }

                                                    function nameFormatterIncarichi(value) {
                                                        if (value == 't') {
                                                            return '<div style="text-align: center;"><i class="fas fa-circle" title="incarichi in corso" style="color:#f2d921"></i></div>';
                                                        } else if (value == 'f') {
                                                            return '<div style="text-align: center;"><i class="fas fa-circle" title="nessun incarico in corso" style="color:#ff0000"></i></div>';
                                                        }
                                                    }

                                                    function nameFormatterEdit(value) {

                                                        return '<a class="btn btn-warning btn-sm" title="Vai ai dettagli" href=./dettagli_segnalazione.php?id=' + value + '>' + value + '<!--i class="fas fa-edit"></i--></a>';

                                                    }

                                                    function nameFormatterMappa1(value, row) {
                                                        //var test_id= row.id;
                                                        return ' <button type="button" class="btn btn-info btn-sm" title="anteprima mappa" data-toggle="modal" data-target="#myMap' + value + '"><i class="fas fa-map-marked-alt"></i></button> \
    <div class="modal fade" id="myMap'+ value + '" role="dialog"> \
    <div class="modal-dialog"> \
      <div class="modal-content">\
        <div class="modal-header">\
          <button type="button" class="close" data-dismiss="modal">&times;</button>\
          <h4 class="modal-title">Anteprima segnalazione '+ value + '</h4>\
        </div>\
        <div class="modal-body">\
        <iframe class="embed-responsive-item" style="width:100%; padding-top:0%; height:600px;" src="./mappa_leaflet.php#17/'+ row.lat + '/' + row.lon + '"></iframe>\
        </div>\
        <!--div class="modal-footer">\
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
        </div-->\
      </div>\
    </div>\
  </div>\
</div>';
                                                    }
                                                </script>

                                            </table>


                                        </div>
                                        <!-- /.table-responsive -->
                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                    </div>


                    <div id="mappa_segnalazioni">

                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fa fa-map-marked-alt fa-fw"></i> Mappa segnalazioni e presidi in corso
                                <div class="pull-right">
                                    <div class="btn-group">
                                        <a class="btn btn-default btn-xs" href="mappa_segnalazioni.php">
                                            <i class="fas fa-expand-arrows-alt"></i> Ingrandisci mappa</a>
                                        <a class="btn btn-default btn-xs" href="elenco_segnalazioni.php">
                                            <i class="fas fa-list"></i> Elenco segnalazioni</a>

                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <iframe style="width:100%;height: 600px;position:relative"
                                src="./mappa_leaflet.php"></iframe>
                            <!-- /.panel-body -->
                        </div>
                    </div>

                    <div id="presidi_mobili">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <i class="fas fa-ambulance"></i> Elenco presidi mobili in corso
                                <div class="pull-right">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-xs dropdown-toggle"
                                            data-toggle="dropdown">
                                            Altro
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu pull-right" role="menu">
                                            <li class="divider"></li>
                                            <li><a href="elenco_sopralluohi mobili.php">Vai all'elenco di tutti i
                                                    presidi mobili</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- /.panel-heading -->
                            <div class="panel-body">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="table-responsive">

                                            <table id="pres" class="table-hover" data-toggle="table"
                                                data-url="./tables/griglia_sopralluoghi_mobili.php?f=prima_pagina"
                                                data-show-export="true" data-search="false" data-click-to-select="true"
                                                data-pagination="true" data-sidePagination="true"
                                                data-show-refresh="true" data-show-toggle="false"
                                                data-show-columns="true" data-toolbar="#toolbar">

                                                <thead>
                                                    <tr>
                                                        <!--th data-field="state" data-checkbox="true"></th-->
                                                        <th data-field="id_stato_sopralluogo" data-sortable="true"
                                                            data-formatter="presidiFormatter" data-visible="true">Stato
                                                        </th>
                                                        <!--th data-field="tipo_provvedimento" data-sortable="true" data-visible="true">Tipo</th-->
                                                        <th data-field="descrizione" data-sortable="true"
                                                            data-visible="true">Descrizione</th>
                                                        <th data-field="data_ora_invio" data-sortable="true"
                                                            data-visible="true">Data e ora<br>assegnazione</th>
                                                        <th data-field="descrizione_uo" data-sortable="true"
                                                            data-visible="true">Squadra</th>
                                                        <th data-field="componenti" data-sortable="true"
                                                            data-visible="true">Componenti</th>
                                                        <!--th data-field="id_evento" data-sortable="true"  data-visible="true">Id<br>evento</th-->
                                                        <!--th data-field="time_start" data-sortable="true"  data-visible="true">Ora<br>inizio</th>
								<th data-field="time_stop" data-sortable="true"  data-visible="true">Ora<br>fine</th>
								<th data-field="note" data-sortable="false" data-visible="true" >Note</th-->
                                                        <th data-field="id" data-sortable="false"
                                                            data-formatter="presidiFormatterEdit" data-visible="true">
                                                            Dettagli</th>
                                                        <!--th data-field="id_segnalazione" data-sortable="false" data-formatter="nameFormatterEdit1" data-visible="true" >Segnalazione</th-->
                                                    </tr>
                                                </thead>
                                            </table>
                                            <script>
                                                function presidiFormatter(value) {
                                                    if (value == 2) {
                                                        return '<i class="fas fa-play" style="color:#5cb85c"></i> Preso in carico';
                                                    } else if (value == 3) {
                                                        return '<i class="fas fa-stop"></i> Chiuso';
                                                    } else if (value == 1) {
                                                        return '<i class="fas fa-exclamation" style="color:#ff0000"></i>Da prendere in carico';
                                                    }

                                                }

                                                function presidiFormatterEdit(value) {

                                                    return '<a class="btn btn-warning" href=./dettagli_sopralluogo_mobile.php?id=' + value + '> <i class="fas fa-edit"></i> </a>';

                                                }
                                            </script>

                                        </div>
                                        <!-- /.table-responsive -->
                                    </div>
                                </div>
                                <!-- /.row -->
                            </div>
                            <!-- /.panel-body -->
                        </div>
                        <!-- /.panel -->
                    </div>


                </div>
                <!-- /.col-lg-8 -->
                <!--div class="col-lg-4">
                
                <?php echo $note_debug; ?>
				<br>

				<br>
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
                        
                    </div-->




                <!-- /.panel -->

                <!--style type="text/css">
                    .twitter-timeline {
							  max-width: 100%;
							  max-height: 1000px;
							}
                    </style-->






                <!--div class="panel panel-default">
                     <div class="panel-heading">
                            <i class="fab fa-twitter fa-fw"></i> Twitter
                        </div>
                        <div class="panel-body" style="max-height:1000px;overflow-y: scroll;">
                     <a class="twitter-timeline" href="https://twitter.com/ProtCivileGE?ref_src=twsrc%5Etfw">
                     Tweets by ProtCivileGE</a> 
                     <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>
                     </div>
                     </div-->









                <!--div class="panel panel-default">
                        <div class="panel-heading">
                            <i class="fa fa-tasks"></i> Gestione eventi
                        </div>
                        <div class="panel-body">
                            <div id="morris-donut-chart"></div>
                            <a href="dettagli_evento.php" class="btn btn-default btn-block">Visualizza dettagli</a>
                        </div>
                        
                    </div-->



                <!--/div-->
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->


            <?php
            $query = "SELECT count(id) FROM segnalazioni.v_segnalazioni;";
            $result = pg_query($conn, $query);
            while ($r = pg_fetch_assoc($result)) {
                $segn_tot = $r["count"];
            }

            // segnalazioni in lavorazione
            $query = "SELECT count(id) FROM segnalazioni.v_segnalazioni WHERE in_lavorazione='t';";
            $result = pg_query($conn, $query);
            while ($r = pg_fetch_assoc($result)) {
                $segn_lav = $r["count"];
            }



            require("contatori_evento_embed.php");
            ?>








        </div>
        <!-- /#page-wrapper -->

    </div>
    <!-- /#wrapper -->

    <?php

    require("./footer.php");

    require("./req_bottom.php");


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



        window.addEventListener("hashchange", function () { scrollBy(0, -70) })

    </script>


</body>

</html>