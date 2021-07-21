<?php 

$subtitle="Gestione squadre"

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
if ($profilo_sistema <= 8){
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
                <div class="col-md-12">
                    <h3>Creazione squadre <i class="fas fa-arrow-right"></i> <!--/h3-->
                <!--/div-->
                <!-- /.col-md-12 -->
            <!--/div-->
            <!-- /.row -->

            <!--div class="row"-->
            <!--div class="col-md-6"-->
            <?php
				//echo $uo_inc;
				//echo '<br>';
				//echo $profilo_squadre;
				if ($check_operatore == 0){
					echo '<!--h4--><i class="fas fa-minus-circle"></i> L\'utente non è autorizzato a creare nuove squadre</h3><hr> ';
				} else {
				?>
					<!--h3--><button type="button" class="btn btn-info"  data-toggle="modal" data-target="#new_squadra"><i class="fas fa-plus"></i> Nuova squadra </button></h3>
					<!-- Modal incarico-->
					<div id="new_squadra" class="modal fade" role="dialog">
					  <div class="modal-dialog">
					
					    <!-- Modal content-->
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal">&times;</button>
					        <h4 class="modal-title">Nuova squadra</h4>
					      </div>
					      <div class="modal-body">
					      
					
					        <form autocomplete="off" action="squadre/nuova_squadra.php?id=<?php echo $id_lavorazione; ?>&s=<?php echo $id; ?>" method="POST">
							
							<!-- Evento -->
							<div class="form-group">
				            <label for="nome"> Evento</label> <font color="red">*</font>  
				 				<?php 
				           $len=count($eventi_attivi);	               
								                
								if($len==1) {   
							   ?>
				
				
				                <select readonly="" class="form-control"  name="evento" required>
				                 
				                    <?php 
				                     for ($i=0;$i<$len;$i++){
				                      
				                        echo '<option name="evento" value="'.$tipo_eventi_attivi[0][0].'">'. $tipo_eventi_attivi[0][1].' (id='.$tipo_eventi_attivi[0][0].')</option>';
				                      }
				                    ?>
				                  </select>
				                                  <small id="eventohelp" class="form-text text-muted">Un solo evento attivo.</small>
				             
				            <?php } else {
				            	?>
				
				                  <select class="form-control"  name="evento" required>
				                     <option value=''>Seleziona un evento tra quelli attivi </option>
				                    <?php 
				                     for ($i=0;$i<$len;$i++){
				                      
				                        echo '<option name="evento" value="'.$tipo_eventi_attivi[$i][0].'">'. $tipo_eventi_attivi[$i][1].' (id='.$tipo_eventi_attivi[$i][0].')</option>';
				                      }
				                    ?>
				                  </select>
				
				            	<?php
				            	}
				            	?>
				              
				            </div>
							
					
					             <div class="form-group">
										 <label for="descrizione"> Nome squadra </label> <font color="red">*</font>
					                <input type="text" name="nome" class="form-control" required="">
							      </div>  
					            
					            <div class="form-group">
					              <label for="tipo_segn">Chi gestisce la squadra:</label> <font color="red">*</font>
					                            <select class="form-control" name="afferenza" id="afferenza" required="" >
					                            <!--option name="afferenza" value="" > Specifica quale Unità Operativa gestirà la squadra </option-->

					                    <option name="afferenza" value="<?php echo $cod_profilo_squadra;?>" ><?php echo $descrizione_profilo_squadra;?></option>
					             
					
					             </select>            
					             </div>
					            <div class="form-group">
					                <input type="checkbox" class="form-check-input" name="permanente" id="permanente">
    									<label class="form-check-label" for="permanente">Rendi squadra permanente <br>
    									</label>
    									<br>
    									<small>Verrà creata in automatico una squadra con lo stesso nome anche per gli eventi futuri
    									</small>           
					             </div>
					
					        <button  id="conferma" type="submit" class="btn btn-primary">Crea squadra</button>
					            </form>
					
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
					      </div>
					    </div>
					
					  </div>
					</div>
					
					
					
				<?php
				} // chiudo if check operatore
				?>
			</div>
            </div>
			<hr>
			
			<div class="row">
			<div style="text-align: center;">
			<h2>Elenco squadre <i> <?php echo $descrizione_profilo_squadra; ?> <?php //echo '('.$profilo_squadre.')';?></i></h2>
			</div>
			
			</div>
			<div class="row">
                <div class="col-md-6">
                    <div style="text-align: center;">
                    <h3 style="padding-top: 120px; margin-top: -120px;"  id="play">
                    <i class="fas fa-play"></i>
                    Squadre attive o attivabili
                    <i class="fas fa-play"></i>
                    </h3>
                    </div>


			
			  <table  id="t_squadre" class="table-hover" 
			  style="word-break:break-all; word-wrap:break-word;" 
			  data-toggle="table" data-url="./tables/griglia_squadre.php?p=<?php echo $profilo_squadre;?>&t=1" 
			  data-show-export="false" data-search="true" 
			  data-click-to-select="true" data-pagination="false" 
			  data-sidePagination="true" data-show-refresh="true" 
			  data-show-toggle="false" data-show-columns="true" 
			  data-toolbar="#toolbar">

				<thead>
				
			 	<tr>
            <!--th data-field="state" data-checkbox="true"></th-->
            <!--th data-field="id" data-sortable="false"  data-visible="true">ID</th-->
            <th data-field="nome" data-sortable="true"  data-visible="true">Nome</th>
            <!--th style="word-break:break-all; word-wrap:break-word; " data-field="evento" data-sortable="true"  data-visible="true">Evento</th>
	         <th data-field="afferenza" data-sortable="true"  data-visible="true" >Afferenza</th-->
	    	
            <th data-field="stato" data-sortable="true" data-formatter="nameFormatter1" data-visible="true" >Stato</th>
            <!--th data-field="id" data-visible="true" data-formatter="nameFormatter0" >Aggiorna stato</th-->
			<th data-field="capo_squadra" data-visible="true" data-formatter="nameFormatter00" >Telegram</th>
            <th data-field="num_componenti" data-sortable="true" data-formatter="nameFormatter2" data-visible="true" >Num</th>
            <th data-field="componenti" data-sortable="true" data-formatter="nameFormatter2" data-visible="true" >Componenti</th>
            <?php
				if ($check_operatore == 1){
				?>
				<th data-field="id" data-sortable="false" data-formatter="nameFormatter" data-visible="true" >Edit</th>
				<!--th data-field="cf" data-sortable="false" data-formatter="nameFormatter1" data-visible="true" >Edit<br>permessi</th-->            
				<?php
				}
				?>
				</tr>
				</thead>
				
				</table>
				
				
				<script>
				    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
				    var $table = $('#t_squadre');
				    $(function () {
				        $('#toolbar').find('select').change(function () {
				            $table.bootstrapTable('destroy').bootstrapTable({
				                exportDataType: $(this).val()
				            });
				        });
				    })
				</script>
				

				

			 </div>
 
 
 
 
 
 
 
<div class="col-md-6">
       <div style="text-align: center;">
        <h3 style="padding-top: 120px; margin-top: -120px;"   id="pause">
        <i class="fas fa-stop"></i>
        Squadre da attivare
        <i class="fas fa-stop"></i>
        </h3>
        </div>


				<table  id="t_squadre2" class="table-hover" 
				data-toggle="table" 
				data-url="./tables/griglia_squadre.php?p=<?php echo $profilo_squadre;?>&t=0" 
				data-show-export="false" data-search="true" 
				data-click-to-select="true" data-pagination="false" data-sidePagination="true" 
				data-show-refresh="true" data-show-toggle="false" data-show-columns="true" 
				data-toolbar="#toolbar">
				        
				        
				<thead>
				
			 	<tr>
            <!--th data-field="state" data-checkbox="true"></th-->
            <!--th data-field="id" data-sortable="false"  data-visible="true">ID</th-->
            <th data-field="nome" data-sortable="true"  data-visible="true">Nome</th>
            <!--th style="word-break:break-all; word-wrap:break-word; " data-field="evento" data-sortable="true"  data-visible="true">Evento</th>
	         <th data-field="afferenza" data-sortable="true"  data-visible="true" >Afferenza</th-->
	    	
            <th data-field="stato" data-sortable="true" data-formatter="nameFormatter1" data-visible="true" >Stato</th>
            <!--th data-field="id" data-visible="true" data-formatter="nameFormatter0" >Aggiorna stato</th-->
            <th data-field="num_componenti" data-sortable="true" data-formatter="nameFormatter2" data-visible="true" >Num</th>
            <th data-field="componenti" data-sortable="true" data-visible="true" >Componenti</th>
            <?php
				if ($check_operatore == 1){
				?>
				<th data-field="id" data-sortable="false" data-formatter="namehideFormatter" data-visible="true" >Edit</th>
				<!--th data-field="id" data-sortable="false" data-formatter="hideFormatter" data-visible="true" ></th-->
				<!--th data-field="cf" data-sortable="false" data-formatter="nameFormatter1" data-visible="true" >Edit<br>permessi</th-->
				<?php
				}
				?>
				</tr>
				</thead>
				
				</table>
				
				
				<script>
				    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
				    var $table = $('#t_squadre2');
				    $(function () {
				        $('#toolbar2').find('select').change(function () {
				            $table.bootstrapTable('destroy').bootstrapTable({
				                exportDataType: $(this).val()
				            });
				        });
				    })
				</script>
				
				
				

			 </div> 
 
 
 
 
 
            </div>
            <hr>
            <div class="row">
                
 
 
 
 
 
 
 
<div class="col-md-6">
                   <div style="text-align: center;">
                    <h3 id="hidden">
                    <i class="fas fa-eye-slash"></i>
                    Squadre nascoste
                    <i class="fas fa-eye-slash"></i>
                    </h3>
                    </div>


				<table  id="t_squadre2" class="table-hover" 
				 data-toggle="table" 
				data-url="./tables/griglia_squadre.php?p=<?php echo $profilo_squadre;?>&t=2" 
				data-show-export="false" data-search="true" data-click-to-select="true" 
				data-pagination="false" data-sidePagination="true" data-show-refresh="true" 
				data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
				        
				        
				<thead>
				
			 	<tr>
            <!--th data-field="state" data-checkbox="true"></th-->
            <!--th data-field="id" data-sortable="false"  data-visible="true">ID</th-->
            <th data-field="nome" data-sortable="true"  data-visible="true">Nome</th>
            <!--th style="word-break:break-all; word-wrap:break-word; " data-field="evento" data-sortable="true"  data-visible="true">Evento</th>
	         <th data-field="afferenza" data-sortable="true"  data-visible="true" >Afferenza</th-->
	    	
            <th data-field="stato" data-sortable="true" data-formatter="nameFormatter1" data-visible="true" >Stato</th>
            <!--th data-field="id" data-visible="true" data-formatter="nameFormatter0" >Aggiorna stato</th-->
            <th data-field="num_componenti" data-sortable="true" data-formatter="nameFormatter2" data-visible="true" >Num</th>
            <th data-field="componenti" data-sortable="true" data-visible="true" >Componenti</th>
            <?php
				if ($check_operatore == 1){
				?>
				<!--th data-field="id" data-sortable="false" data-formatter="nameFormatter" data-visible="true" >Edit</th-->
				<th data-field="id" data-sortable="false" data-formatter="nohideFormatter" data-visible="true" ></th>
				<!--th data-field="cf" data-sortable="false" data-formatter="nameFormatter1" data-visible="true" >Edit<br>permessi</th-->            
				<?php
				}
				?>
				</tr>
				</thead>
				
				</table>
				
				
				<script>
				    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
				    var $table = $('#t_squadre2');
				    $(function () {
				        $('#toolbar2').find('select').change(function () {
				            $table.bootstrapTable('destroy').bootstrapTable({
				                exportDataType: $(this).val()
				            });
				        });
				    })
				</script>
				
				
				

			 </div> 
			 
			 
			 <!--div class="col-md-6">
                    <div style="text-align: center;">
                    <h3>
                    <i class="fas fa-play"></i>
                    To do..
                    <i class="fas fa-play"></i>
                    </h3>
                    </div>


			
			  <table  id="t_squadre" class="table-hover" style="word-break:break-all; word-wrap:break-word; " data-toggle="table" data-url="./tables/griglia_squadre.php?p=<?php echo $profilo_squadre;?>&t=1" data-height="900"  data-show-export="false" data-search="true" data-click-to-select="true" data-pagination="false" data-sidePagination="true" data-show-refresh="true" data-show-toggle="false" data-show-columns="true" data-toolbar="#toolbar">
				        
				        
				<thead>
				
			 	<tr>

            <th style="word-break:break-all; word-wrap:break-word; " data-field="nome" data-sortable="true"  data-visible="true">Nome</th>
        
            <th data-field="stato" data-sortable="true" data-formatter="nameFormatter1" data-visible="true" >Stato</th>

            <th data-field="num_componenti" data-sortable="true" data-formatter="nameFormatter2" data-visible="true" >Num</th>
            <th data-field="componenti" data-sortable="true" data-formatter="nameFormatter2" data-visible="true" >Componenti</th>
            <?php
				if ($check_operatore == 1){
				?>
				<th data-field="id" data-sortable="false" data-formatter="nameFormatter" data-visible="true" >  </th>

				<?php
				}
				?>
				</tr>
				</thead>
				
				</table>
				
				
				<script>
				    // DA MODIFICARE NELLA PRIMA RIGA L'ID DELLA TABELLA VISUALIZZATA (in questo caso t_volontari)
				    var $table = $('#t_squadre');
				    $(function () {
				        $('#toolbar').find('select').change(function () {
				            $table.bootstrapTable('destroy').bootstrapTable({
				                exportDataType: $(this).val()
				            });
				        });
				    })
				</script>
				

				

			 </div-->

            </div>

<script>
function nameFormatter(value,row, index) {
	return '<a href="./edit_squadra.php?id='+ value + '" class="btn btn-warning btn-sm" \
	title="Modifica componenti squadra e definisci ulteriori info" role="button"><i class="fa fa-users" aria-hidden="true"></i>\
	</a> ';
}

function hideFormatter(value,row, index) {
	if (row.num_componenti==0){
		return '<a href="./squadre/nascondi_squadra.php?id='+ value + '" class="btn btn-warning btn-sm" \
		title="Nascondi squadra" role="button"><i class="fas fa-eye-slash"></i>\
		</a>';
	}
}

function namehideFormatter(value,row, index) {
	if (row.num_componenti==0){
		return '<a href="./edit_squadra.php?id='+ value + '" class="btn btn-warning btn-sm" \
        title="Modifica componenti squadra e definisci ulteriori info" role="button"><i class="fa fa-users" aria-hidden="true"></i>\
         </a> <a href="./squadre/nascondi_squadra.php?id='+ value + '" class="btn btn-warning btn-sm" \
		title="Nascondi squadra" role="button"><i class="fas fa-eye-slash"></i>\
		</a>';
	}
}

function nohideFormatter(value,row, index) {
	if (row.num_componenti==0){
		return '<a href="./squadre/vis_squadra.php?id='+ value + '" class="btn btn-warning btn-sm" \
		title="Visualizza squadra" role="button"><i class="fas fa-eye"></i>\
		</a>';
	}
}

function nameFormatter0(value, row, index) {
	if (row.id_stato==2){
		return '<a href="./squadre/riposo.php?id='+ value + '" class="btn btn-danger" \
        title="Imposta a riposo" role="button"><i class="fa fa-stop" aria-hidden="true"></i>\
         </a>';
		} else if (row.id_stato==3) {
			return '<a href="./squadre/play.php?id='+ value + '" class="btn btn-success" \
        title="Imposta come a disposizione" role="button"><i class="fa fa-play" aria-hidden="true"></i>\
        </a>';
		}         
}

function nameFormatter00(value, row, index) {
	if (row.capo_squadra == 't' && row.operativo=='t'){
		return '<i class="fas fa-check-circle" aria-hidden="true" style="color: green; font-size: xx-large;"></i>';
	}else if (row.capo_squadra == 't' && row.operativo!='t') {
			return '<i class="fas fa-times-circle" aria-hidden="true" style="color: orange; font-size: xx-large;"></i>';
	} 
}
				
function nameFormatter1(value, row, index) {
	if (row.id_stato==1){
		return '<a href="./dettagli_'+row.descrizione+'.php?id='+row.id_incarico+'">\
		<i class="fa fa-play" aria-hidden="true" title="'+value+' - Vai all\'incarico o presidio"></i>\
		</a>';
	} else if (row.id_stato==2) {
		if (<?php echo $check_operatore; ?> ==1){
			if (row.num_componenti > 0) {
				return  '<i class="fa fa-pause" aria-hidden="true" title="'+value+'"></i> - \
				<a href="./squadre/riposo.php?id='+ row.id + '" class="btn btn-danger btn-sm" \
        		title="Imposta come a riposo" role="button"><i class="fa fa-stop" aria-hidden="true"></i>\
         	</a>\
		 		<a href="./squadre/svuota.php?id='+ row.id + '" class="btn btn-warning btn-sm" \
        		title="Svuota squadra" role="button"><i class="fa fa-user-alt-slash" aria-hidden="true"></i>\
         	</a>';
         } else {
         	return  '<i class="fa fa-pause" aria-hidden="true" title="'+value+'"></i>';
         }
		} else {
         	return  '<i class="fa fa-pause" aria-hidden="true" title="'+value+'"></i>';
      }
	} else if (row.id_stato==3) {
			if (<?php echo $check_operatore; ?> ==1) { 
				return '<i class="fa fa-stop" aria-hidden="true" title="'+value+'"></i> - \
				<a href="./squadre/play.php?id='+ row.id + '" class="btn btn-success btn-sm" \
        		title="Imposta come a disposizione" role="button"><i class="fa fa-play" aria-hidden="true"></i>\
         	</a>\
		 		<a href="./squadre/svuota.php?id='+ row.id + '" class="btn btn-warning btn-sm" \
        		title="Svuota squadra" role="button"><i class="fa fa-user-alt-slash" aria-hidden="true"></i>\
         	</a>';
         } else {
         	return '<i class="fa fa-stop" aria-hidden="true" title="'+value+'"></i>';
         }
		}
}

function nameFormatter2(value) {
	if (value<1){
		return ' <i class="fas fa-exclamation-circle fa-2x"  style="color:red" aria-hidden="true"></i> ' +value ;
	} else {
		return  value;
	}
}
				</script> 
 
 
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>

<script type="text/javascript" >

(function($) {
    
    $.fn.bmdIframe = function( options ) {
        var self = this;
        var settings = $.extend({
            classBtn: '.bmd-modalButton',
            defaultW: 640,
            defaultH: 360
        }, options );
      
        $(settings.classBtn).on('click', function(e) {
          var allowFullscreen = $(this).attr('data-bmdVideoFullscreen') || false;
          
          var dataVideo = {
            'src': $(this).attr('data-bmdSrc'),
            'height': $(this).attr('data-bmdHeight') || settings.defaultH,
            'width': $(this).attr('data-bmdWidth') || settings.defaultW
          };
          
          if ( allowFullscreen ) dataVideo.allowfullscreen = "";
          
          // stampiamo i nostri dati nell'iframe
          $(self).find("iframe").attr(dataVideo);
        });
      
        // se si chiude la modale resettiamo i dati dell'iframe per impedire ad un video di continuare a riprodursi anche quando la modale è chiusa
        this.on('hidden.bs.modal', function(){
          $(this).find('iframe').html("").attr("src", "");
        });
      
        return this;
    };
  
})(jQuery);

jQuery(document).ready(function(){
  jQuery("#myModal").bmdIframe();
});


</script>
    

</body>

</html>
