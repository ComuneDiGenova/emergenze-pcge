<?php 


$subtitle="Convocazione COC Direttivo";


$getfiltri=$_GET["f"];
$filtro_evento_attivo=$_GET["a"];

//echo $filtro_evento_attivo; 


$uri=basename($_SERVER['REQUEST_URI']);
//echo $uri;

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberta" >

    <title>Registro COC Direttivo</title>
<?php 
require('./req.php');

require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');

require('./check_evento.php');

$subtitle="Convocazione COC Direttivo";

/* if ($profilo_ok==3){
	$subtitle="Elenco utenti presenti";
} else {
	$subtitle="Elenco utenti (tua Unità Operativa)";
} */
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
            <!--div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Elenco segnalazioni</h1>
                </div>
            </div-->
			<!-- <br> -->
			<!--?php
            if ($profilo_ok==3){
				$filter = ' ';
			} else if($profilo==8){
				$filter= ' WHERE id_profilo=\''.$profilo.'\' and nome_munic = \''.$livello.'\' ';
			} else {
				$filter= ' WHERE id_profilo=\''.$profilo.'\' ';
			} 
						
			/*$query="SELECT count(matricola_cf) From \"users\".\"v_utenti_sistema\" ".$filter." ;";*/
            $query="SELECT count(matricola_cf) From \"users\".\"v_utenti_presenti\" ".$filter.";";
            $result = pg_prepare($conn, "myquery0", $query);
            $result = pg_execute($conn, "myquery0", array());
            

			while($r = pg_fetch_assoc($result)) {
                echo '<i class="fas fa-user-check  faa-ring animated"></i> '. $r['count']. ' utenti presenti registrati da telegram';
				/* if ($profilo_ok==3){
					echo '<i class="fas fa-users  faa-ring animated"></i> '. $r['count']. ' utenti registrati a sistema';
				} else {
					echo '<i class="fas fa-users faa-ring animated"></i> '. $r['count']. ' utenti della tua unit� operativa abilitati';
				} */
				
			}?-->
									
            <!-- <br> -->
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header noprint">Ultima Convocazione COC Direttivo
					<button class="btn btn-info noprint" onclick="printClass('fixed-table-container')">
					<i class="fa fa-print" aria-hidden="true"></i> Stampa tabella </button>
                    <?php if ($profilo_ok<=3){ ?>
                        <button type="button" class="btn btn-info noprint"  data-toggle="modal" data-target="#conv_coc">
                        <i class="fas fa-bullhorn"></i> Convoca COC </button>
                    <?php
                    }
                    ?>
					</h1>

			<!-- Modal convocazione coc-->
            <div id="conv_coc" class="modal fade" role="dialog">
				  <div class="modal-dialog">
				
				    <!-- Modal content-->
				    <div class="modal-content">
				      <div class="modal-header">
				        <button type="button" class="close" data-dismiss="modal">&times;</button>
				        <h4 class="modal-title">Convocazione COC</h4>
				      </div>
				      <div class="modal-body">
      

        			    <form autocomplete="off" enctype="multipart/form-data" action="./convocazione_coc.php" method="POST">
                            <div class="form-group">
                                <label for="addMatricolaCf" >Testo Convocazione <font color="red">*</font></label>                 
                                <textarea class="form-control" name="testoCoC" id="testoCoC" rows="10" required></textarea>
                            </div>
                            <button  id="convoca" type="submit" class="btn btn-primary" name="Add">Invia Convocazione COC</button>
                        </form>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
                        </div>
                    </div>

                </div>
                </div>
            <div class="row">

		<?php //echo $profilo_ok;?>
		<br>
		<?php //echo $livello1;?>
	
        <div class="noprint" id="toolbar">
            <select class="form-control">
                <option value="">Esporta i dati visualizzati</option>
                <option value="all">Esporta tutto (lento)</option>
                <option value="selected">Esporta solo selezionati</option>
            </select>
        </div>
        
        <table  id="convocati" class="table-hover" data-toggle="table" 
        data-url="./tables/griglia_convocazione_coc.php?p=<?php echo $profilo_ok;?>&l=<?php echo $livello1;?>" 
       data-show-export="true" data-export-type=['json', 'xml', 'csv', 'txt', 'sql', 'excel', 'doc', 'pdf']
        data-search="true" data-click-to-select="true" data-show-print="true" data-pagination="true" 
        data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true"
        data-filter-control="false" data-toolbar="#toolbar">
        
        
<thead>

 	<tr>
            <th data-field="state" data-checkbox="true"></th>
            <!--th data-field="matricola_cf" data-sortable="true" data-visible="true" >CF/matricola</th--> 
            <!--th data-field="tipo_provvedimento" data-sortable="true" data-visible="true">Tipo</th-->
            <th data-field="funzione" data-sortable="true"  data-visible="true">Funzione</th>
            <th data-field="cognome" data-sortable="true"  data-visible="true">Cognome</th>
            <th data-field="nome" data-sortable="true"   data-visible="true">Nome</th>
            <!--th data-field="data_invio" data-sortable="true"  data-visible="true" data-filter-control="select">Data/ora invio notifica</th-->
            <th data-field="data_invio" data-sortable="true"  data-visible="true">Data/ora invio notifica</th>
            <th data-field="lettura" data-sortable="true" data-formatter="letturaFormatter" data-visible="true">Conferma lettura</th>
            <th data-field="data_conferma" data-sortable="true"  data-visible="true">Data/ora conferma lettura</th>
			<th data-field="data_invio_conv" data-sortable="true"  data-visible="true">Data/ora invio Convocazione</th>
            <th data-field="lettura_conv" data-sortable="true" data-formatter="letturaFormatter2" data-visible="true">Conferma Convocazione</th>
            <th data-field="data_conferma_conv" data-sortable="true"  data-visible="true">Data/ora conferma convocazione</th>
            <!--?php
            if ($profilo_ok==3){?>
                <th data-field="id" data-sortable="false" data-formatter="nameFormatterEdit1" data-visible="true" >Termina turno</th>
                <th data-field="id" data-sortable="false" data-formatter="nameFormatterEdit2" data-visible="true" >Modifica turno</th-->
            <!--?php
                }
            ?-->
    </tr>
</thead>

</table>


<script>
    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
    var $table = $('#convocati');
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


 function letturaFormatter(value) {
        if (value=='t'){
        		return '<center><i class="far fa-check-circle" style="color:#14c717; font-size: xx-large;"></i></center>';
        } else {
        	   return '<center><i class="far fa-times-circle" style="color:#ff0000; font-size: xx-large;"></i></center>';
        }

    }

function letturaFormatter2(value, row) {
        if (row.data_invio_conv != null && value =='t'){
        		return '<center><i class="fas fa-check-circle" style="color:#14c717; font-size: xx-large;"></i></center>';
        } else if (row.data_invio_conv != null && value != 't') {
        	   return '<center><i class="fas fa-times-circle" style="color:#ff0000; font-size: xx-large;"></i></center>';
        } else{
            return '<center>-</center>';
        }

    }

 function nameFormatterEdit(value) {
    if (value.length==16){
		return '<a class="btn btn-warning" href="./update_volontario.php?id='+value+'"> <i class="fas fa-edit"></i> </a>';
	} else {
		return '<a class="btn btn-warning" href="./permessi.php?id='+value+'"> <i class="fas fa-edit"></i> </a>';
    }
 }

 function nameFormatterEdit1(value, row) {
	return '<a class="btn btn-warning" href=./chiudi_presenza.php?id='+row.id+'> <i class="fas fa-user-times"></i> </a>';
    }

function nameFormatterEdit2(value, row) {
	//return '<a class="btn btn-warning" href=./chiudi_presenza.php?id='+row.id+'> <i class="fas fa-user-times"></i> </a>';
    //aggiungere la parte che consente di modificare data e turno (vedi isernia)
    //verificare se è installato il boostrap validator per validazione dei form
    return' <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#updatePres'+row.id+'" title="Modifica dettagli turno" onclick="checkVal('+row.id+')"><i class="fas fa-user-edit"></i></button>\
            <div class="myclass modal fade" id="updatePres'+row.id+'" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">\
          <div class="modal-dialog modal-dialog-centered" role="document">\
            <div class="modal-content">\
              <div class="modal-header">\
                <h5 class="modal-title" id="exampleModalLabelBci'+row.id+'">Dettagli turno</h5>\
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">\
                  <span aria-hidden="true">&times;</span>\
                </button>\
              </div>\
              <div class="modal-body">\
            <form id="detTurno'+row.id+'" action="modifica_turno.php?id='+row.id+'" method="post" enctype="multipart/form-data">\
            <div class="form-group">\
            <label>Data Inizio Turno</label><br><br>\
              <input type="text" class="form-control" name="dataInizioTurno" id="dataInizioTurno'+row.id+'" value="'+row.data_inizio+'" style="height: auto;"><br>\
              <div class="help-block with-errors"></div>\
              </div>\
              <label>Durata turno</label>\
              <div class="form-group">\
              <input type="text" class="form-control" name="durataTurno" id="durataTurno'+row.id+'" value="'+row.durata+'"><br>\
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
