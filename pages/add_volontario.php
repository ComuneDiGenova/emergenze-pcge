<?php 

$subtitle="Form aggiunta utente esterno"

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

/*if ($profilo_sistema > 3){
	header("location: ./divieto_accesso.php");
}*/

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
                 
                    <h1 class="page-header"> <i class="fa fa-user-plus"></i> Aggiunta all'anagrafe del personale esterno <br><small>(volontari, personale delle Municipalizzate, etc)</small></h1>
                </div>


        <form action="add_volontario2.php" method="POST">
        
                      <h4><i class="fa fa-address-card"></i> Credenziali:</h4> 
        
        
            <div class="form-group">
                <label for="nome"> Nome</label> <font color="red">*</font>
                <input type="text" name="nome" class="form-control" required>
              </div>
            <div class="form-group">
                <label for="nome"> Cognome</label> <font color="red">*</font>
                <input type="text" name="cognome" class="form-control" required>
              </div>


            <div class="form-group">
                <label for="CF"> Codice fiscale:</label> <font color="red">*</font>
                <input type="text"  pattern=".{16,16}" maxlenght="16" name="CF" class="form-control"  required>
                <small id="emailHelp" class="form-text text-muted">Il Codice Fiscale è obbligatorio e sarà utilizzato per accedere al sistema tramite le credenziali <a target="_new" href="https://www.spid.gov.it/">SPID</a>.</small>

              </div>
              

                <!--div class="form-group"  data-date-format="yyyy-mm-dd" data-provide="datepicker" >
						<label for="data_nascita" >Data di nascita (AAAA-MM-GG) </label>                 
						<input type="text" class="form-control" name="data_nascita"/>
						<div class="input-group-addon">
							<span class="glyphicon glyphicon-th"></span>
						</div>
					</div-->   


            <div class="form-group">

                <label for="data_nascita"> Data nascita:</label> <font color="red">*</font>

              <div class="form-row">
   
   
    <div class="form-group col-md-4">
                  <select class="form-control"  name="dd" required>
                  <option name="birth_day" value="" > Giorno </option>
                    <?php 
                      $start_date = 1;
                      $end_date   = 31;
                      for( $j=$start_date; $j<=$end_date; $j++ ) {
                        echo '<option value='.$j.'>'.$j.'</option>';
                      }
                    ?>
                  </select>
    </div>
   


    <div class="form-group col-md-4">

                  <select class="form-control" name="mm"  required>                            
                  <option name="birth_month" value="" > Mese </option>
                    <?php for( $m=1; $m<=12; ++$m ) { 
                      setlocale(LC_TIME, 'it_IT.iso88591');
                      #$month_label = date('F', mktime(0, 0, 0, $m, 1));
                      $month_label = strftime('%B', mktime(0, 0, 0, $m, 1));

                      
                    ?>
                      <option value="<?php echo $m; ?>"><?php echo $month_label; ?></option>
                    <?php } ?>
                  </select> 
                  </div>
   
    <div class="form-group col-md-4">
                  <select class="form-control"  name="yyyy"  required>
                  <option name="birth_year" value="" > Anno </option>  
                    <?php 
                      $year = date('Y');
                      $min = $year - 110;
                      $max = $year;
                      for( $i=$max; $i>=$min; $i-- ) {
                        echo '<option value='.$i.'>'.$i.'</option>';
                      }
                    ?>
                  </select>
                 </div>
                 </div>
                
                <!--input type="number" name="day" min="1" max="31">-
                <input type="number" name="month" min="1" max="12">
                <input type="Year" name="month" min="1900" max="<?php echo date("Y"); ?>"-->
              </div>


              <!-- https://sister.agenziaentrate.gov.it/CitizenArCom/InitForm.do?ric=report -->
              <div class="form-group">
              <label for="naz">Nazionalità:</label> <font color="red">*</font>
                            <select class="form-control" name="naz" id="naz">
                            <option name="naz" value="ITALIA" > ITALIA </option>
            <?php            
            $query2="SELECT * From \"varie\".\"stati_2018\";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
            ?>    
                    <option name="naz" value="<?php echo $r2['nome'];?>" ><?php echo $r2['nome'];?></option>
             <?php } ?>

             </select>            
             </div>




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


              
                
                           
              <h4><i class="fa fa-building"></i> Residenza / Domicilio:</h4> 

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
              <label for="provincia">Provincia:</label> <font color="red">*</font>
                            <select class="selectpicker show-tick form-control" data-live-search="true" onChange="getCivico(this.value);" required>
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
              <label for="comune">Comune:</label> <font color="red">*</font>
                <select class="form-control" name="comune" id="comune-list" class="demoInputBox" required>
                <option value="">Seleziona il comune</option>
            </select>         
             </div>

             <div class="form-group">
                <label for="nome"> Indirizzo</label> <font color="red">*</font>
                <input type="text" name="indirizzo" class="form-control" required>
                <small id="addrHelp" class="form-text text-muted">Specificare via/piazza/località, numero civico ed eventualmente interno.</small> 
              </div>
              
              
              <div class="form-group">
                <label> CAP:</label>
                <input type="text" name="cap" class="form-control" maxlength="5">
                <small id="capHelp" class="form-text text-muted">Codice di Avviamento Postale.</small> 
              </div>


                    <h4><i class="fa fa-phone"></i> Contatti:</h4>      
                    
                     
              <div class="form-group">
                <label for="nome"> Telefono principale</label> <font color="red">*</font>
                <input type="text" name="telefono1" class="form-control" required>
                <small id="addrHelp" class="form-text text-muted">Preferibilmente recapito cellulare disponibile in emergenza.</small> 
              </div>

              <div class="form-group">
                <label for="nome"> Telefono secondario</label> 
                <input type="text" name="telefono2" class="form-control">
              </div>
              
              <div class="form-group">
                <label for="nome"> Fax</label>
                <input type="text" name="fax" class="form-control">
              </div>
              
              <div class="form-group">
                <label for="nome"> Mail</label> <font color="red">*</font>
                <input type="email" name="mail" class="form-control" required>
              </div>           
              
              <script>
            function getUO_II(val) {
	            $.ajax({
	            type: "POST",
	            url: "get_UO_II.php",
	            data:'cod='+val,
	            success: function(data){
		            $("#UO_II_list").html(data);
	            }
	            });
            }

            </script>
				<script>
            function getUO_III(val) {
	            $.ajax({
	            type: "POST",
	            url: "get_UO_III.php",
	            data:'cod='+val,
	            success: function(data){
		            $("#UO_III_list").html(data);
	            }
	            });
            }

            </script>


             <div class="form-group">
              <label for="provincia">Unità operativa I livello:</label> <font color="red">*</font>
                            <select name="UO_I" class="selectpicker show-tick form-control" data-live-search="true" onChange="getUO_II(this.value);" required="">
                            <option value="">Seleziona...</option>
            <?php            
            $query2="SELECT * From \"users\".\"uo_1_livello\";";
	        $result2 = pg_query($conn, $query2);
            //echo $query1;    
            while($r2 = pg_fetch_assoc($result2)) { 
                //$valore=  $r2['id']. ";".$r2['descrizione'];            
            ?>
                        
                    <option name="UO_I" value="<?php echo $r2['id1'];?>" ><?php echo $r2['descrizione'];?></option>
             <?php } ?>

             </select>            
             </div>


            <!--div class="form-group">
              <label for="comune">Unità operative II livello (demo):</label> 
                <select class="form-control" name="UO_II" id="UO_II_list" class="demoInputBox" onChange="getUO_III(this.value);">
                <option value="">Seleziona ..</option>
            </select>         
             </div>
              
             <div class="form-group">
              <label for="comune">Unità operative III livello (demo):</label> 
                <select class="form-control" name="UO_III" id="UO_III_list" class="demoInputBox">
                <option value="">Seleziona ..</option>
            </select>         
             </div--> 
              
              
              
               
               <div class="form-group">
                <label for="nome"> Numero tessera Gruppo Genova</label> 
                <input type="text" name="num_GG" class="form-control">
              </div>                    


            <button type="submit" class="btn btn-primary">Aggiungi</button>
            </form>
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


    

</body>

</html>
