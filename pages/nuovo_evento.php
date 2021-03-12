<?php 

$subtitle="Creazione nuovo evento"

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

if ($profilo_sistema >=3){
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
            <div class="row">
                <div class="col-lg-12">
                
                <br>
                <button type="button" class="btn btn-success btn-lg"  data-toggle="modal" data-target="#modal_n_e">Nuovo evento</button>


<!-- Modal -->
<div id="modal_n_e" class="modal fade" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Nuovo evento</h4>
      </div>
      <div class="modal-body">
      

        <form action="eventi/nuovo_evento2.php?id='<?php echo $r['cf']?>'" method="POST">

              
              
              <div class="form-group">
              <label for="tipo_evento">Tipo:</label> <font color="red">*</font>
                            <select name="tipo_evento"  class="selectpicker show-tick form-control" data-live-search="true" onChange="getCivico(this.value);" required>
                            <option value="">Seleziona una tipologia di evento</option>
            <?php            
            $query2="SELECT * From \"eventi\".\"tipo_evento\" WHERE valido='TRUE';";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                $valore=  $r2['id']. ";".$r2['descrizione'];            
            ?>
                        
                    <option name="cod" value="<?php echo $r2['id'];?>" ><?php echo $r2['descrizione'];?></option>
             <?php } ?>

             </select>            
             </div>
             
             
             
              <div class="form-group">
              <div class="checkbox">
    					<label>
      					<input type="checkbox" class="check" id="checkAll" checked> <b>Tutti i municipi</b>
    					</label>
  					</div>
				<?php            
            $query2="SELECT codice_mun, nome_munic From geodb.municipi;";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>
            	    <div class="checkbox">
    					<label>
      					<input type="checkbox"  class="check" name="check[]" checked value="<?php echo $r2['codice_mun'] ?>"> <?php echo $r2['nome_munic'] ?>
    					</label>
 						 </div>
 						 <?php
                //$valore=  $r2['codice_mun']. ";".$r2['nome_munic'];            
             }            
            ?>


                   </div>
           
           
           <div class="form-group">
                <label for="note"> Descrizione </label>
  						<textarea class="form-control" rows="5" name="note" id="note"></textarea>

              </div>
              
              
            <button type="submit" class="btn btn-primary">Crea</button>
            </form>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
      </div>
    </div>

  </div>
</div>            




                    <!--h1 class="page-header">Titolo pagina</h1-->
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <br><br>
            <div class="row">

            </div>
            <!-- /.row -->
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


   <script>
              
              $("#checkAll").click(function () {
    				$(".check").prop('checked', $(this).prop('checked'));
					});
					</script>              

    

</body>

</html>
