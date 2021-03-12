<?php 

$subtitle="Elenco ultimi bollettini meteo"

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
                    <h1 class="page-header">Bollettini meteo ARPA e PC</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <br><br>
            <div class="row">


        <div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
        
        <table  id="t_volontari" class="table-hover" style="word-break:break-all; word-wrap:break-word; " data-toggle="table" data-url="./tables/griglia_bollettini.php" data-height="900"  data-show-export="true" data-search="true" data-click-to-select="true" data-pagination="true" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <th data-field="icon" data-sortable="false" data-formatter="nameFormatterIcon" data-visible="true"></th>
            <th data-field="tipo" data-sortable="false"  data-visible="true">Tipo</th>
            <th data-field="ente" data-sortable="false"  data-visible="true">Ente competente</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="data_ora_emissione" data-sortable="true"  data-visible="true">Data e ora<br>emissione</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="data_download" data-sortable="true"  data-visible="true">Data e ora<br>download</th>
			<th style="word-break:break-all; word-wrap:break-word; " data-field="nomepdf" data-sortable="true"  data-visible="true">Nome</th>
            <th data-field="nomefile" data-sortable="false" data-formatter="nameFormatter" data-visible="true" > Altro </th>            

    </tr>
</thead>

</table>


<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#t_volontari');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val()
            });
        });
    })
</script>

<br><br>

<script>


 function nameFormatter(value) {
        return '<a href="../../bollettini/'+ value + '" target="_blank" class="btn btn-warning" title="Download PdfM" role="button"><i class="fa fa-file-pdf" aria-hidden="true"></i> </a>' ;
    }


  function nameFormatterIcon(value) {
        return '<i class="fas fa-'+ value +'"></i>' ;
    }


</script>





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
