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

//echo $profilo_sistema;

//$check_operatore=0;
if (($profilo_sistema > 0 AND $profilo_sistema <= 1) OR $profilo_sistema==11){
	$check_operatore=1;
}
//echo $check_operatore;

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
                    <h1 class="page-header">Elenco mail per invio aggiornamenti meteo</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            <?php
				if ($check_operatore ==1){
				?>	
				<button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_mail">
				<i class="fas fa-plus"></i> Aggiungi mail</button>
				</h4>
				<?php
				}
				?>
				
				
			<!-- Modal add mail-->
			<div id="new_mail" class="modal fade" role="dialog">
			  <div class="modal-dialog">
			
				<!-- Modal content-->
				<div class="modal-content">
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Inserire aggiornamento meteo</h4>
				</div>
				<div class="modal-body">
 
				<form autocomplete="off" enctype="multipart/form-data" action="report/nuova_mail.php" method="POST">

				<div class="form-group">
					<label for="aggiornamento">Descrizione</label> <font color="red">*</font>
					<input type="text" class="form-control" id="desc" name="desc" placeholder="Inserisci una descrizione" required>
				</div>

				<div class="form-group">
					<label for="aggiornamento">Mail</label> <font color="red">*</font>
					<input type="email" class="form-control" id="mail" name="mail" aria-describedby="emailHelp" placeholder="Inserisci nuovo indirizzo mail a cui mandare le info meteo" required>
				</div>

				<button  id="conferma" type="submit" class="btn btn-primary">Aggiungi</button>
				</form>

				</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
			  </div>
			</div>

		  </div>
		</div>
		
	
		
		
		
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
		data-url="./tables/griglia_mail_meteo.php" data-height="900"  data-show-export="true" data-search="true" 
		data-click-to-select="true" data-pagination="true" data-sidePagination="false" data-show-refresh="true" 
		data-page-size=75 data-page-list=[10,25,50,75,100,200,500]
		data-show-toggle="false" data-show-columns="true" data-filter-control="true" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="descrizione" data-sortable="true"  data-visible="true" data-filter-control="input">Descrizione</th>
	        <th data-field="mail" data-sortable="true" data-visible="true" data-filter-control="input" >Mail</th>
			<th data-field="valido" data-sortable="true" data-formatter="nameFormatterBoolean" data-visible="true">Attiva</th>
            <?php
				if ($check_operatore == 1){
				?>
					<th data-field="id" data-sortable="false" data-formatter="nameFormatter1" data-visible="true" > Edit </th>           
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

        return '<a href="./edit_mail_meteo.php?id=\''+ value + '\'" class="btn btn-warning" title="Modifica dati" role="button"><i class="fa fa-user-edit" aria-hidden="true"></i> </a>';
    }



function nameFormatterBoolean(value, row) {
        //return '<i class="fas fa-'+ value +'"></i>' ;
        if (value=='t'){
        		return '<i class="fas fa-check faa-ring animated" title="Invio a questa mail attivo" style="color:#5cb85c"></i> - \
				<a href="./report/pausa_mail_meteo.php?id='+ row.id + '" class="btn btn-sm btn-danger" title="Mail attiva. Clicca per disattivare invio a questa mail" role="button">\
				<i class="fa fa-pause" aria-hidden="true"></i> </a>';
        } else if (value=='f') {
        	   return '<i class="fas fa-times faa-ring animated" title="Invio a questa mail disattivo" style="color:#ff0000"></i> - \
			   <a href="./report/attiva_mail_meteo.php?id='+ row.id + '" class="btn btn-sm btn-success" title="Mail disattiva. Clicca per attivare invio a questa mail" role="button">\
				<i class="fa fa-play" aria-hidden="true"></i> </a>';
        }
        else {
        		return ' - ';
        }
    }


function nameFormatter1(value, row, field) {
	//var test_id= row.id;
	return' <button type="button" class="btn btn-info" data-toggle="modal" data-target="#editmail'+value+'"><i class="fas fa-user-edit"></i></button> \
    <div class="modal fade" id="editmail'+value+'" role="dialog"> \
    <div class="modal-dialog"> \
      <div class="modal-content">\
        <div class="modal-header">\
          <button type="button" class="close" data-dismiss="modal">&times;</button>\
          <h4 class="modal-title">Edit mail '+value+'</h4>\
        </div>\
        <div class="modal-body">\
		<form action="report/edit_mail.php?id= '+value+'" method="POST">\
			<div class="form-group">\
				<label for="aggiornamento">Descrizione</label> <font color="red">*</font>\
				<input type="text" class="form-control" id="desc" name="desc" value='+JSON.stringify(row.descrizione)+' required>\
			</div>\
			<div class="form-group">\
				<label for="aggiornamento">Mail</label> <font color="red">*</font>\
				<input type="email" class="form-control" id="mail" name="mail" aria-describedby="emailHelp" value='+row.mail+' required>\
			</div>\
			<button  type="submit" class="btn btn-primary">Modifica</button>\
		</form>\
        </div>\
        </div>\
    </div>\
  </div>\
</div>';
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
