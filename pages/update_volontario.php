<?php 

$subtitle="Dettagli volontario"

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

$cf=pg_escape_string($_GET["id"]);
//$cf=$_GET["id"];

$id=$cf;

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
                 
                    <h1 class="page-header"> <i class="fa fa-user"></i> Dettagli utente esterno
                    
                    <?php
                    $check_profilo=0;
                    $query="SELECT * From \"users\".\"v_utenti_esterni_telegram2\" where \"cf\"='$cf';"; 
                    
                    $result = pg_query($conn, $query);
	                //$rows = array();
	                //echo $result;
	                while($r = pg_fetch_assoc($result)) {
                    		//$rows[] = $r;
                    		echo $r['cognome']. " ".$r['nome'];
                    		$profilo=$r['id_profilo'];
    		        ?>
                    
                    </h1>
                </div>


            
            <br><br>
            <div class="row">
              <div class="col-lg-3 col-md-auto">

			<!--b>MATRICOLA</b>: <?php echo $r['matricola'] ?>  <br>
		
			<br-->
            <h4> <i class="fa fa-address-book"></i> Informazioni anagrafiche 
            </h4>
            <b>Cognome e nome</b>: <?php echo $r['cognome']. " ".$r['nome']  ?>  <br>
            <b>Codice fiscale</b>: <?php echo $r['cf'] ?>  <br>
            <b>Data di nascita</b>: <?php echo $r['data_nascita'] ?>  <br>   
            <b>Nazionalità:</b> <?php echo $r['nazione_nascita'] ?> <br>
            <br>
			
			<?php
			if ($profilo_sistema<=3 OR $id=='\''.$CF.'\''){
			?>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_ana"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			}
			?>

  



<!-- Modal -->
<div id="modal_ana" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit anagrafica volontario</h4>
      </div>
      <div class="modal-body">
      

        <form action="update_v/anagrafica.php?id=<?php echo $r['cf']?>" method="POST">

              <div class="form-group">
                <label for="cognome"> Cognome</label> *
                <input type="text" value='<?php echo $r['cognome']?>' name="cognome" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="nome"> Nome</label> *
                <input type="text" value='<?php echo $r['nome']?>' name="nome" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="cf"> CF</label> *
                <input type="text" pattern=".{16,16}" maxlenght="16" value='<?php echo $r['cf']?>' name="cf" class="form-control" required>
              </div>           
              <div class="form-group">
                <label for="data_nascita">Data di nascita</label>
                <input type="text" name="data_nascita" value='<?php echo $r['data_nascita']?>' class="form-control">
              </div>            
              <div class="form-group">
                <label for="nazione_nascita">Nazione di nascita</label>
                <input type="text" name="nazione_nascita" value='<?php echo $r['nazione_nascita']?>' class="form-control">
              </div>    
              
            <button type="submit" class="btn btn-primary">Aggiorna</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>            




			</div><div class="col-lg-3 col-md-auto">

         
            <h4> <i class="fa fa-home"></i> Residenza
            
            </h4>
				<b>Indirizzo</b>: <?php echo $r['indirizzo'] ?><br>				
				&emsp;&emsp;&emsp;&emsp;&emsp;<?php echo $r['cap']." - ". $r['comune']. " (".$r['provincia']  ?>)<br>
           
           <br>
		   <?php
			if ($profilo_sistema<=3 OR $id=='\''.$CF.'\''){
			?>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_res"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			}
			?>
			
<!-- Modal -->
<div id="modal_res" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit residenza</h4>
      </div>
      <div class="modal-body">
      

        <form action="update_v/indirizzo.php?id=<?php echo $r['cf']?>" method="POST">

              <div class="form-group">
                <label for="indirizzo"> Indirizzo</label>
                <input type="indirizzo" value='<?php echo $r['indirizzo']?>' name="indirizzo" class="form-control" >
              </div>
              <div class="form-group">
                <label for="cap"> cap</label>
                <input type="text" value='<?php echo $r['cap']?>' name="cap" class="form-control">
              </div>
              
              
            <button type="submit" class="btn btn-primary">Aggiorna indirizzo</button>
            </form>


            <form action="update_v/comune.php?id=<?php echo $r['cf']?>" method="POST">            
            <br><br>
            <b>Comune:</b> <?php echo $r['comune']." (" .$r['provincia']. ")" ?>


				
		<label for="cat" class="auto-length">
			<input type="checkbox" name="cat" id="cat">
			Modificare comune 
		</label>
		
		            <br><br>


	
	<!--div class="form-group">
              <label for="naz">Comune residenza:</label> <font color="red">*</font>
                            <select class="form-control" name="naz" id="naz">
                            <option name="naz" value="" > Scegli un comune..</option>
            <?php            
            $query2="SELECT * From \"varie\".\"comuni_italia\";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>    
                    <option name="naz" value="<?php echo $r2['Denominazione in italiano'];?>" ><?php echo $r2['Denominazione in italiano'];?></option>
             <?php } ?>

             </select>            
             </div-->


              
                
                           

            <script>
            function getCivico(val) {
	            $.ajax({
	            type: "POST",
	            url: "get_comune.php",
	            data:'cod='+val,
	            success: function(data){
		            $("#comune-list").html(data);
	            }
	            });
            }

            </script>



             <div class="form-group">
              <label for="provincia">Provincia:</label> <font color="red"></font>
                            <select disabled="" name="provincia" id="provincia-list" class="selectpicker show-tick form-control" data-live-search="true" onChange="getCivico(this.value);" >
                            <option value="">Seleziona la provincia</option>
            <?php            
            $query2="SELECT * From \"varie\".\"province\";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                $valore=  $r2['cod']. ";".$r2['nome'];            
            ?>
                        
                    <option name="cod" value="<?php echo $r2['cod'];?>" ><?php echo $r2['nome'];?></option>
             <?php } ?>

             </select>            
             </div>


            <div class="form-group">
              <label for="comune">Comune:</label> <font color="red"></font>
                <select disabled="" class="form-control" name="comune" id="comune-list" class="demoInputBox" >
                <option value="">Seleziona il comune</option>
            </select>         
             </div>           


          
       
 


         
              
              
            <button disabled="" type="submit" class="btn btn-primary" id="btn_comune">Aggiorna comune</button>
            </form>
            

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div> 




              




				</div><div class="col-lg-3 col-md-auto">
				<h4> <i class="fa fa-phone"></i> Contatti
				
            </h4>
				<b>Telefono principale</b>: <?php echo $r['telefono1']?><br>
            <b>Mail</b>: <?php echo $r['mail'] ?>  <br>
            <b>Telefono secondario</b>: <?php echo $r['telefono2'] ?>  <br>   
            <b>Fax:</b> <?php echo $r['fax'] ?> <br>
            <b>ID Telegram:</b> <?php echo $r['telegram_id'] ?> 
            <?php
            if ($profilo_sistema<=3 OR $id=='\''.$CF.'\'' ){
              if($r['telegram_id']!=''){
                if ($r['telegram_attivo'] == 't'){
            ?>
            <a class="btn btn-warning btn-sm" title="Disattiva notifiche telegram" href="./disattiva_notifiche.php?cf=<?php echo $r['cf']?>"> <i class="fas fa-bell-slash"></i> </a>
            <!--a class="btn btn-warning btn-sm" title="Disattiva notifiche telegram" href="update_v/disattiva_notifiche.php?cf=<?php echo $r['cf']?>"> <i class="fas fa-bell-slash"></i> </a-->
            <?php
                }else{
            ?>
             <a class="btn btn-primary btn-sm" title="Attiva notifiche telegram" href="./attiva_notifiche.php?cf=<?php echo $r['cf']?>"> <i class="fas fa-bell"></i> </a>
             <!--a class="btn btn-primary btn-sm" title="Attiva notifiche telegram" href="update_v/attiva_notifiche.php?cf=<?php echo $r['cf']?>"> <i class="fas fa-bell"></i> </a-->
            <br>
            <?php
                }
              }
            }
            ?>
				<br>
				
			<?php
			if ($profilo_sistema<=3 OR $id=='\''.$CF.'\''){
			?>
				<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_con"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			}
			?>	


<!-- Modal -->
<div id="modal_con" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit contatto volontario</h4>
      </div>
      <div class="modal-body">
      

        <form action="update_v/contatti.php?id=<?php echo $r['cf']?>" method="POST">

              <div class="form-group">
                <label for="telefono1"> Telefono principale*</label>
                <input type="text" value='<?php echo $r['telefono1']?>' name="telefono1" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="mail"> Mail*</label>
                <input type="email" value='<?php echo $r['mail']?>' name="mail" class="form-control" required>
              </div>
              <div class="form-group">
                <label for="telefono2"> Telefono secondario</label>
                <input type="text" value='<?php echo $r['telefono2']?>' name="telefono2" class="form-control">
              </div>           
              <div class="form-group">
                <label for="fax">Fax</label>
                <input type="text" name="fax" value='<?php echo $r['fax']?>' class="form-control">
              </div>
              <div class="form-group">
                <label for="fax">ID Telegram</label>
                <?php
                  if ($profilo != ''){
                ?>
                  <input type="text" name="telegram_id" value='<?php echo $r['telegram_id']?>' class="form-control">
                <?php
                  }else{
                ?>
                  <input type="text" name="telegram_id" value='<?php echo $r['telegram_id']?>' class="form-control" disabled title="Devi assegnare un profilo all'utente per inserire l'ID Telegram">
                <?php
                  }
                ?>
              </div>           
                
              
            <button type="submit" class="btn btn-primary">Aggiorna</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>    				
				
				
				
				
				
				
				
				</div><div class="col-lg-3 col-md-auto">
				<h4> <i class="fa fa-plus"></i> Altro</h4>
				<b>N° Gruppo Genova</b>: <?php echo $r['numero_gg']?>
				
				<?php
			if ($profilo_sistema<=3 OR $id=='\''.$CF.'\''){
			?>
				<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal_gg"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			}
			?>
				<br><br>
				
            <b>UO I livello</b>: <?php echo $r['livello1'] ?>  
			<?php
			if ($profilo_sistema<=3 OR $id=='\''.$CF.'\''){
			?>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal_I_livello"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			}
			?>
            <br><br>
            
            <b>UO II livello</b>: <?php echo $r['livello2'] ?>
			<?php
			if ($profilo_sistema<=3 OR $id=='\''.$CF.'\''){
			?>
            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal_II_livello"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			}
			?>
            <br><br>
            
            
            <!--b>UO III livello </b>: <?php echo $r['livello3'] ?>
            <?php
			if ($profilo_sistema<=3 OR $id=='\''.$CF.'\''){
			?>
			<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal_III_livello"> 
				     <i class="fa fa-pencil-alt"></i>        
            </button>
			<?php
			}
			?>
            <br><br-->
            </div>
            
            
            
				<!-- Modal -->
<div id="modal_I_livello" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit I livello</h4>
      </div>
      <div class="modal-body">
      

        <form action="update_v/I_livello.php?id=<?php echo $r['cf']?>" method="POST">

              <div class="form-group">
              <label for="I_livello">Unità operativa I livello (demo):</label> <font color="red">*</font>
             <select class="selectpicker show-tick form-control" data-live-search="true" name="id1" id="id1" required>
             <option value="">Seleziona...</option>
            <?php            
            $query2="SELECT * From \"users\".\"uo_1_livello\";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                //$valore=  $r2['id']. ";".$r2['descrizione'];            
            ?>
                        
                    <option name="id1" value="<?php echo $r2['id1'];?>" ><?php echo $r2['descrizione'];?></option>
             <?php } ?>

             </select>            
             </div>
             


            <button type="submit" class="btn btn-primary">Aggiorna</button>
            </form>



      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div> 


				<!-- Modal -->
<div id="modal_II_livello" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit II livello</h4>
      </div>
      <div class="modal-body">

<form action="update_v/II_livello.php?id=<?php echo $r['cf']?>" method="POST">

              <div class="form-group">
              <label for="II_livello">Unità operativa II livello (demo):</label> <font color="red"></font>
                            <select class="selectpicker show-tick form-control" data-live-search="true" name="id2" id="id2" required>
                            <option value="">Seleziona...</option>
            <?php            
            $query2="SELECT * From \"users\".\"uo_2_livello\" WHERE id1= ".$r['id1'].";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                //$valore=  $r2['id']. ";".$r2['descrizione'];            
            ?>
                        
                    <option name="id2" value="<?php echo $r2['id2'];?>" ><?php echo $r2['descrizione'];?></option>
             <?php } ?>

             </select>            
             </div>
             


            <button type="submit" class="btn btn-primary">Aggiorna</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div> 


				<!-- Modal -->
<div id="modal_III_livello" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit III livello</h4>
      </div>
      <div class="modal-body">

<form action="update_v/III_livello.php?id=<?php echo $r['cf']?>" method="POST">

              <div class="form-group">
              <label for="II_livello">Unità operativa III livello (demo):</label> <font color="red">*</font>
                            <select class="selectpicker show-tick form-control" data-live-search="true" name="id3" id="id3" required>
                            <option value="">Seleziona...</option>
            <?php            
            $query2="SELECT * From \"users\".\"uo_3_livello\" WHERE id1= ".$r['id1']." and id2= ".$r['id2'].";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                //$valore=  $r2['id']. ";".$r2['descrizione'];            
            ?>
                        
                    <option name="id3" value="<?php echo $r2['id3'];?>" ><?php echo $r2['descrizione'];?></option>
             <?php } ?>

             </select>            
             </div>
             


            <button type="submit" class="btn btn-primary">Aggiorna</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div> 


<!-- Modal -->
<div id="modal_gg" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Edit numero tessera Gruppo Genova  (volontariato)</h4>
      </div>
      <div class="modal-body">
      

        <form action="update_v/gg.php?id=<?php echo $r['cf']?>" method="POST">

              <div class="form-group">
                <label for="numero_gg"> N° gruppo Genova</label>
                <input type="text" value='<?php echo $r['numero_gg']?>' name="numero_gg" class="form-control">
              </div>
             


            <button type="submit" class="btn btn-primary">Aggiorna</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>  
            
            
            
            
            
            
            <?php
             } #chiudo il while 
             if ($profilo!=''){
               	$check_profilo=1;
               }
             
             
             
             ?>
            
            
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->
    <br><br>    
    
    <hr>
     </div>

		
            <?php
			require('./section_permessi.php')
			?>
           
            
            
            <!-- /.row -->    
    
    
    
    <hr>
<?php 

require('./footer.php');

require('./req_bottom.php');


?>

<script>

(function ($) {
    'use strict';
    
    $('[type="checkbox"').on('change', function () {
        if ($(this).is(':checked')) {
            $('#catName').removeAttr('disabled');
            $('#provincia-list').removeAttr('disabled');
            $('#provincia-list').selectpicker('refresh');
            $('#comune-list').removeAttr('disabled');
            $('#btn_comune').removeAttr('disabled');
            return true;
        }
        $('#catName').attr('disabled', 'disabled');
    });
    
}(jQuery));    

</script>  
    

</body>

</html>
