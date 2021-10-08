<?php 

$subtitle="Gestione Componenti COC Direttivo"

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


if ($profilo_sistema > 3){
	header("location: ./divieto_accesso.php");
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
        <br>
        <?php
            $query="SELECT count(matricola_cf) From users.utenti_coc;";
            $result = pg_prepare($conn, "myquery0", $query);
            $result = pg_execute($conn, "myquery0", array());
            

			while($r = pg_fetch_assoc($result)) {
                echo '<i class="fas fa-user-cog  faa-ring animated"></i> '. $r['count']. ' Componenti del COC Direttivo';
            }
        ?> 
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Elenco Componenti COC Direttivo
                <?php
                    if ($profilo_sistema <= 3){
				?>	
				<button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#new_coc">
				<i class="fas fa-user-plus"></i> Aggiungi Componente </button>
                </h1>
				<?php
				}
				?>

			<!-- Modal aggiungi utente coc-->
            <div id="new_coc" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Aggiungere Componente COC</h4>
				      </div>
				      <div class="modal-body">
      

        			    <form autocomplete="off" enctype="multipart/form-data" action="./add_componente_coc.php" method="POST">
                            <div class="form-group">
                                <label for="addMatricolaCf" >Matricola/CF <font color="red">*</font></label>                 
                                <input type="text" class="form-control" name="addMatricolaCf" id="addMatricolaCf" required>
                            </div>
                            <div class="form-group">
                                <label for="addNome" >Nome <font color="red">*</font></label>                 
                                <input type="text" class="form-control" name="addNome" id="addNome" required>
                            </div> 
                            <div class="form-group">
                                <label for="addCognome" >Cognome <font color="red">*</font></label>                 
                                <input type="text" class="form-control" name="addCognome" id="addCognome" required>
                            </div> 
                            <div class="form-group">
                                <label for="addMail" >Mail <font color="red">*</font></label>                 
                                <input type="text" class="form-control" name="addMail" id="addMail" required>
                            </div> 
                            <div class="form-group">
                                <label for="addTelegram" >ID Telegram <font color="red">*</font></label>                 
                                <input type="text" class="form-control" name="addTelegram" id="addTelegram" required>
                            </div> 
                            <button  id="conferma" type="submit" class="btn btn-primary" name="Add">Aggiungi Componente COC</button>
                        </form>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                        </div>
                    </div>

                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            
            <br>
            <div class="row">


        <div id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
        
        <table  id="t_coc" class="table-hover" style="word-break:break-all; word-wrap:break-word; " data-toggle="table" data-url="./tables/griglia_elenco_coc.php" 
        data-height="900"  data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf'] 
        data-search="true" data-click-to-select="true" data-show-print="true" data-pagination="true" data-sidePagination="true" 
        data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <th data-field="matricola_cf" data-sortable="false"  data-visible="true">CF</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="cognome" data-sortable="true"  data-visible="true">Cognome</th>
            <th style="word-break:break-all; word-wrap:break-word; " data-field="nome" data-sortable="true"  data-visible="true">Nome</th>           
            <?php
				if ($check_operatore == 1){
				?>
            <th data-field="matricola_cf" data-sortable="false" data-formatter="nameFormatter0" data-visible="true" > Edit </th>
            <th data-field="matricola_cf" data-sortable="false" data-formatter="nameFormatter" data-visible="true" > Rimuovi </th>
            <?php
            }
            ?>
            <!--th data-field="cf" data-sortable="false" data-formatter="nameFormatter1" data-visible="true" >Edit<br>permessi</th-->            

    </tr>
</thead>

</table>


<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#t_coc');
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


function nameFormatter(value,row) {

    //return '<a href="./update_volontario.php?id=\''+ value + '\'" class="btn btn-warning" title="Modifica dati" role="button"><i class="fa fa-user-edit" aria-hidden="true"></i> </a> <a href="./elimina_volontario.php?id=\''+ value + '\'" class="btn btn-danger" role="button" title="Elimina persona" ><i class="fa fa-times" aria-hidden="true"></i> </a>';
    return' <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#elimina'+value+'"><i class="fas fa-times"></i></button> \
    <div class="modal fade" id="elimina'+value+'" role="dialog"> \
    <div class="modal-dialog"> \
      <div class="modal-content">\
        <div class="modal-header">\
          <button type="button" class="close" data-dismiss="modal">&times;</button>\
          <h4 class="modal-title">Conferma eliminazione componente COC '+row.cognome+' '+row.nome+'</h4>\
        </div>\
        <div class="modal-body">\
        Vuoi eliminare il componente del COC? clicca qua \
		<a href="./elimina_componente_coc.php?id='+ value + '" class="btn btn-danger" role="button" title="Elimina componente" ><i class="fa fa-times" aria-hidden="true"></i> Elimina </a>\
        </div>\
        <!--div class="modal-footer">\
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>\
        </div-->\
      </div>\
    </div>\
  </div>\
</div>';
}

function nameFormatter0(value, row) {
    //verificare se è installato il boostrap validator per validazione dei form
    //<button type="button" class="btn btn-warning" data-toggle="modal" data-target="#updateCoc'+row.id+'" title="Modifica dati Componente" onclick="checkVal('+row.id+')"><i class="fas fa-user-edit"></i></button>\
    return' <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#updateCoc'+row.id+'" title="Modifica dati Componente"><i class="fas fa-user-edit"></i></button>\
            <div class="myclass modal fade" id="updateCoc'+row.id+'" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">\
          <div class="modal-dialog modal-dialog-centered" role="document">\
            <div class="modal-content">\
              <div class="modal-header">\
                <h5 class="modal-title" id="exampleModalLabelBci'+row.id+'">Dati Componente COC</h5>\
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">\
                  <span aria-hidden="true">&times;</span>\
                </button>\
              </div>\
              <div class="modal-body">\
            <form id="detCoc'+row.id+'" action="modifica_componente_coc.php?id='+row.id+'" method="post" enctype="multipart/form-data">\
            <div class="form-group">\
            <label>Matricola/CF <span style="color:red;">*</span></label><br><br>\
              <input type="text" class="form-control" name="matricolaCf" id="matricolaCf'+row.matricola_cf+'" value="'+row.matricola_cf+'" style="height: auto;" required><br>\
              <div class="help-block with-errors"></div>\
              </div>\
              <label>Nome <span style="color:red;">*</span></label>\
              <div class="form-group">\
              <input type="text" class="form-control" name="nome" id="nome'+row.matricola_cf+'" value="'+row.nome+'" required><br>\
              <div class="help-block with-errors"></div>\
              </div>\
              <label>Cognome <span style="color:red;">*</span></label>\
              <div class="form-group">\
              <input type="text" class="form-control" name="cognome" id="cognome'+row.matricola_cf+'" value="'+row.cognome+'" required><br>\
              <div class="help-block with-errors"></div>\
              </div>\
              <label>Mail <span style="color:red;">*</span></label>\
              <div class="form-group">\
              <input type="text" class="form-control" name="mail" id="mail'+row.matricola_cf+'" value="'+row.mail+'" required><br>\
              <div class="help-block with-errors"></div>\
              </div>\
              <label>ID Telegram <span style="color:red;">*</span></label>\
              <div class="form-group">\
              <input type="text" class="form-control" name="telegramId" id="telegramId'+row.matricola_cf+'" value="'+row.telegram_id+'" required><br>\
              <div class="help-block with-errors"></div>\
              </div>\
              <div class="form-group">\
              <input type="submit" value="Modifica" name="Submit">\
              </div>\
            </form>\
              </div>\
              <div class="modal-footer">\
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Chiudi</button>\
                <!--button type="button" class="btn btn-primary">Save changes</button-->\
              </div>\
            </div>\
          </div>\
        </div>' ;
    }

/*function nameFormatter0(value) {

	if (value=='t'){
        return '<i class="fa fa-play" aria-hidden="true"></i>';
	} else if (value=='f') {
		  return '<i class="fa fa-pause" aria-hidden="true"></i>';
	} else {
		return '';
	}
}

function nameFormatter_val(value) {

	if (value=='t'){
        return '<i class="fa fa-user-check" aria-hidden="true" title="Operativo"></i>';
	} else if (value=='f') {
		  return '<i class="fa fa-user-cross" aria-hidden="true" title="Non operativo"></i>';
	} else {
		return '';
	}
}



  function nameFormatter1(value) {

        return '<a href="./permessi.php?id='+ value + '" class="btn btn-warning" title="Modifica permessi" role="button"><i class="fa fa-user-lock" aria-hidden="true"></i> </a>';
    }*/

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
