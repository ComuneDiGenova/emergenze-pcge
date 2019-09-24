<?php 

$subtitle="Storico sala emergenze"

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

require('/home/local/COMGE/egter01/emergenze-pcge_credenziali/conn.php');

require('./check_evento.php');

$check_operatore=0;
if ($profilo_sistema <= 3){
	$check_operatore=1;
}


if ($profilo_sistema > 6){
	header("location: ./divieto_accesso.php");
}

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
                    <h1 class="page-header">Storico turni sala emergenze</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <div class="row">
        <div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
       
        <table  id="t_cse" class="table-hover" style="word-break:break-all; word-wrap:break-word; " data-toggle="table" 
		data-url="./tables/griglia_se.php"  data-show-export="true" data-search="true" data-click-to-select="true" 
		data-pagination="true" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" 
		data-show-columns="true" data-toolbar="#toolbar">
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <th data-field="matricola_cf" data-sortable="false"  data-visible="true">Matr/CF</th>
			<th data-field="tipo" data-sortable="false"  data-visible="true">Tipologia</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="cognome" data-sortable="true"  data-visible="true">Cognome</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="nome" data-sortable="true"  data-visible="true">Nome</th>
			<th data-field="data_start" data-sortable="true"  data-visible="true" >Inizio</th>
	        <th data-field="data_end" data-sortable="true"  data-visible="true" >Fine</th>
    </tr>
</thead>
</table>
<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#t_se');
    $(function () {
        $('#toolbar').find('select').change(function () {
            $table.bootstrapTable('destroy').bootstrapTable({
                exportDataType: $(this).val()
            });
        });
    })
</script>
            </div> <!-- /.row -->
			
			
			
			
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


    

</body>

</html>
