<?php 

$subtitle="Mail di notifica incarichi - Elenco"

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
if ($profilo_sistema <= 4.){
	$check_operatore=1;
}


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
                    <h1 class="page-header">Elenco mail per invio notifica incarichi</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <?php
				if ($check_operatore == 0){
					echo '<h4><i class="fas fa-minus-circle"></i> L\'utente non è autorizzato a modificare le mail di contatto. Per segnalare la modifica di alcuni dati si prega di inviare comunicazioni all\'<a href="mailto:adminemergenzepc@comune.genova.it">amministratore di sistema</a></h4><hr> ';
				}
				?>
            <br>
            <div class="row">


        <div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
        
        <table  id="t_mail" class="table-hover" style="word-break:break-all; word-wrap:break-word; " data-toggle="table" 
		data-url="./tables/griglia_mail.php" data-height="900"  data-show-export="true" data-search="true" data-click-to-select="true" 
		data-pagination="true" data-sidePagination="false" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
		data-page-size=75 data-page-list=[10,25,50,75,100,200,500] 
		data-filter-control="true" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="tipo" data-sortable="true" data-filter-control="select" data-visible="true">Tipo</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="descrizione" data-sortable="true" data-filter-control="input" data-visible="true">Descrizione<br>U.O.</th>
	          <th data-field="mails" data-sortable="true"  data-visible="true" data-filter-control="input" >Mail</th>
              <!--th data-field="ids_telegram" data-sortable="true"  data-visible="true" data-filter-control="input" >Telegram</th-->
            <?php
				if ($check_operatore == 1){
				?>
                    <th data-field="ids_telegram" data-sortable="true"  data-visible="true" data-filter-control="input" >Telegram</th>
					<th data-field="cod" data-sortable="false" data-formatter="nameFormatter" data-visible="true" > Edit </th>           
				<?php
				}else{
				?>
                <th data-field="ids_telegram" data-sortable="false" data-formatter="nameFormatter2" data-visible="true">Telegram</th>
                <?php
				}
				?>
    </tr>
</thead>

</table>


<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#t_mail');
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

        return '<a href="./edit_mail_uo.php?id=\''+ value + '\'" class="btn btn-warning" title="Modifica dati" role="button"><i class="fa fa-user-edit" aria-hidden="true"></i> </a>';
    }



function nameFormatter0(value) {

	if (value=='t'){
        return '<i class="fa fa-play" aria-hidden="true"></i>';
	} else if (value=='f') {
		  return '<i class="fa fa-pause" aria-hidden="true"></i>';
	} else {
		return '';
	}
}


  function nameFormatter1(value) {

        return '<a href="./permessi.php?id=\''+ value + '\'" class="btn btn-warning" title="Modifica permessi" role="button"><i class="fa fa-user-lock" aria-hidden="true"></i> </a>';
    }

function nameFormatter2(value) {
    console.log(value)
    if (value!=null){
        return '<i class="fas fa-check-circle" style="color:green;" aria-hidden="true"></i>';
	} else {
		return '-';
	}
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
