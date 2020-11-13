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

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

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
		data-show-columns="true" data-toolbar="#toolbar" data-filter-control="true" 
  data-show-search-clear-button="true" >
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <th data-field="matricola_cf" data-sortable="false" data-filter-control="input" data-visible="true">Matr/CF</th>
			<th data-field="tipo" data-sortable="false"  data-filter-control="select" data-visible="true">Tipologia</th>
            <th style="word-break:break-all; word-wrap:break-word;" data-filter-control="input" data-field="cognome" data-sortable="true"  data-visible="true">Cognome</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="nome" data-sortable="true" data-filter-control="input" data-visible="true">Nome</th>
			<th data-field="data_start" data-sortable="true" data-filter-control="input" data-visible="true" >Inizio</th>
	        <th data-field="data_end" data-sortable="true" data-filter-control="input" data-visible="true" >Fine</th>
			<th data-field="modificato" data-sortable="true" data-formatter="nameFormatterMod" data-visible="true" > </th>
			<?php 
			if ($profilo_sistema==1){
			?>
			<th data-field="table" data-sortable="true" data-formatter="nameFormatterEdit" data-visible="true" >Modifica</th>
			<?php 
			}
			?>
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

function nameFormatterEdit(value,row) {
	return '<a class="btn btn-warning" title="Visualizza dettagli e edita" href="./correggi_turni.php?t='+value+'&m='+row.matricola_cf+'&s='+row.data_start+'&e='+row.data_end+'"> <i class="fas fa-edit"></i> </a>';
}


function nameFormatterMod(value) {
	if (value=='t'){
		return '<i title="Record precedentemente modificato" class="fas fa-exclamation"></i>';
	} else {
		return '-';
	}
}
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
