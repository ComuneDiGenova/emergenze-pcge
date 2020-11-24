<?php 

session_start();
//require('../validate_input.php');;
//require('../validate_input.php');;

include explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php';

$id=$_GET["id"];



$query= "SELECT nome, id_evento FROM users.t_squadre where id=".$id.";";


$result = pg_query($conn, $query);
while($r = pg_fetch_assoc($result)) {
	$nome_sq=$r['nome'];
	if ($r["id_evento"]==''){
		$permanente="(Squadra permanente)";	
	} else {
		$permanente="(<i class=\"fas fa-hourglass-half\"></i> Si tratta 
		di una squadra creata per l'evento ".$r["id_evento"].", 
		al termine dell'evento verrà rimossa)
		<a class='btn btn-sm btn-info' href=squadre/rendi_permanente.php?id=".$id." ><i class=\"fas fa-hourglass\"></i> Rendi permanente</a>";
	}
}


$subtitle="Dettagli squadra"

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
?>
<!-- jQuery -->
<script src="../vendor/jquery/jquery.min.js"></script>  
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
            <h2><b>Nome squadra</b>: <?php echo $nome_sq;?> 
            <button type="button" class="btn btn-primary"  data-toggle="modal" data-target="#nome_squadra">
            <i class="fas fa-edit"></i> Cambia nome </button>
            </h2>
            <?php echo $permanente; ?>
            
            

<div id="nome_squadra" class="modal fade" role="dialog">
					  <div class="modal-dialog">
					
					    <!-- Modal content-->
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="close" data-dismiss="modal">&times;</button>
					        <h4 class="modal-title">Cambia nome squadra</h4>
					      </div>
					      <div class="modal-body">
					      
					
					        <form autocomplete="off" action="squadre/edit_nome.php?id=<?php echo $id; ?>" method="POST">
							
							
					             <div class="form-group">
										 <label for="descrizione"> Nome squadra </label> <font color="red">*</font>
					                <input type="text" name="nome" class="form-control" required="" value="<?php echo $nome_sq?>">
							      </div>  
					
					
					        <button  id="conferma" type="submit" class="btn btn-primary">Cambio nome</button>
					            </form>
					
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-default" data-dismiss="modal">Annulla</button>
					      </div>
					    </div>
					
					  </div>
					</div>
					            
            
            
            
            
            <hr>
            <div id="componenti">
            <h2>Componenti squadra:</h2><br>
				<?php
				$check_capo=0; // non ci sono ma diventa 1 se ce ne sono già
				$query="SELECT id, matricola_cf, nome, cognome, max(mail) as mail, max(telefono) as telefono, capo_squadra FROM users.v_componenti_squadre WHERE id=".$id." and data_end is null 
				GROUP BY id, matricola_cf, nome, cognome, capo_squadra;";
				//echo $query;
				if (isset($conn)){
					$result = pg_query($conn, $query);
				} else {
					require(explode('emergenze-pcge',getcwd())[0].'emergenze-pcge/conn.php');
					$result = pg_query($conn, $query);
				}
				while($r = pg_fetch_assoc($result)) {
					if($r['capo_squadra']=='t'){
						$check_capo=1;
					}
				}
				$m=0;
				$result = pg_query($conn, $query);
				while($r = pg_fetch_assoc($result)) {
					$m=$m+1;
					echo '<div class="col-md-2"> <b>Cognome e nome</b>:</div>';
					echo '<div class="col-md-2">'. $r['cognome'].' '.$r['nome'] .'</div>';
					echo '<div class="col-md-3">';
					if ($r['mail']!=''){
						/*if (strlen($r['matricola_cf'])==16){
							echo '('.$r['mail'].')';
						} else {*/
					$edit_mail=$r['mail'];
					?>
					
					
						<!--form class="form-inline" action="./incarichi_interni/cambia_mail.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>" method="POST"-->
						<form class="form-inline">
						<div class="form-group">
							<label for="mail" class="sr-only">Email</label>
							<input type="mail" class="form-control-plaintext" id="mailsq<?php echo $m;?>" name="mailsq<?php echo $m;?>" value="<?php echo $r['mail']; ?>" >
						</div>
						<button  type="submit" class="btn btn-primary" onclick="return editmail_<?php echo $m;?>()">Edit</button>
						</form>
						<script type="text/javascript" >
						function editmail_<?php echo $m;?>() {
							//alert('Test1');
							var mail=document.getElementById('mailsq<?php echo $m;?>').value;
							var dataString='mail='+mail;
							$.ajax({
								type:"post",
								url:"./incarichi_interni/cambia_mail.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>",
								data:dataString,
								cache:false,
								success: function (html) {
									$('#msg').html(html);
								}
								//$('#componenti').load(document.URL +  ' #componenti');
							});
							$('#componenti').load(document.URL +  ' #componenti');
							return false;
						};
						</script>
						
					<?php
						//}	
					} else {
						?>
						<!--form class="form-inline" action="./incarichi_interni/import_mail.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>" method="POST"-->
						<form class="form-inline">
						<div class="form-group">
							<label for="mail" class="sr-only">Email</label>
							<input required="" type="mail" class="form-control-plaintext" id="mailsq<?php echo $m;?>" name="mailsq<?php echo $m;?>" >
						</div>
						<button  type="submit" class="btn btn-primary" onclick="return addmail_<?php echo $m;?>()">Aggiungi mail</button>
						</form>
						
						
						<script type="text/javascript" >
						function addmail_<?php echo $m;?>() {
							//alert('Test1');
							var mail=document.getElementById('mailsq<?php echo $m;?>').value;
							var dataString='mail='+mail;
							$.ajax({
								type:"post",
								url:"./incarichi_interni/import_mail.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>",
								data:dataString,
								cache:false,
								/*success: function (html) {
									$('#msg').html(html);
								}*/
								$('#componenti').load(document.URL +  ' #componenti');
							});
							$('#componenti').load(document.URL +  ' #componenti');
							return false;
						};
						</script>						
						<?php
					}
					echo '</div><div class="col-md-3">';
					if ($r['telefono']!=''){
						/*if (strlen($r['matricola_cf'])==16){
							echo '('.$r['mail'].')';
						} else {*/
					$edit_telefono=$r['telefono'];
					?>
					
					
						<!--form class="form-inline" action="./incarichi_interni/cambia_telefono.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>" method="POST"-->
						<form class="form-inline">
						<div class="form-group">
							<label for="telefono" class="sr-only">Tel</label>
							<input type="telefono" class="form-control-plaintext" id="telsq<?php echo $m;?>" name="telsq<?php echo $m;?>" value="<?php echo $r['telefono']; ?>" >
						</div>
						<button  type="submit" class="btn btn-primary" onclick="return edittel_<?php echo $m;?>()">Edit</button>
						</form>
						<script type="text/javascript" >
						function edittel_<?php echo $m;?>() {
							//alert('Test1');
							var tel=document.getElementById('telsq<?php echo $m;?>').value;
							var dataString='tel='+tel;
							$.ajax({
								type:"post",
								url:"./incarichi_interni/cambia_telefono.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>",
								data:dataString,
								cache:false,
								/*success: function (html) {
									$('#msg').html(html);
								}*/
								//$('#componenti').load(document.URL +  ' #componenti');
							});
							$('#componenti').load(document.URL +  ' #componenti');
							return false;
						};
						</script>
						
					<?php
						//}	
					} else {
						?>
						
						<!--form class="form-inline" action="./incarichi_interni/import_telefono.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>" method="POST"-->
						<form class="form-inline">
						<div class="form-group">
							<label for="telefono" class="sr-only">Tel</label>
							<input required="" type="telefono" class="form-control-plaintext" id="telsq<?php echo $m;?>" name="telsq<?php echo $m;?>">
						</div>
						<button  type="submit" class="btn btn-primary" onclick="return addtel_<?php echo $m;?>()">Aggiungi tel</button>
						</form>
						
						<script type="text/javascript" >
						function addtel_<?php echo $m;?>() {
							//alert('Test1');
							var tel=document.getElementById('telsq<?php echo $m;?>').value;
							var dataString='tel='+tel;
							$.ajax({
								type:"post",
								url:"./incarichi_interni/import_telefono.php?s=<?php echo $id;?>&cf=<?php echo $r['matricola_cf'];?>",
								data:dataString,
								cache:false,
								/*success: function (html) {
									$('#msg').html(html);
								}*/
								//$('#componenti').load(document.URL +  ' #componenti');
							});
							$('#componenti').load(document.URL +  ' #componenti');
							return false;
						};
						</script>
						
						<?php
					}
					
					
					echo '</div>';
					echo '<div class="col-md-2">';
					if($r['capo_squadra']=='t'){
						//echo '<i class="fas fa-user-check"></i>  <a class="btn btn-danger" href="./squadre/elimina_capo_squadra.php?m='.$r['matricola_cf'].'&s='.$r['id'].'"> <i class="fas fa-user-times"></i> Rimuovi come capo squadra </a>';					
						echo '<i class="fas fa-user-check"></i>  <button class="btn btn-danger" onclick="return eliminacs'.$m.'()"> <i class="fas fa-user-times"></i> Rimuovi come capo squadra</button>'; 
					} else {
						//echo '<a class="btn btn-danger" href="./squadre/elimina_componente.php?m='.$r['matricola_cf'].'&s='.$r['id'].'"> <i class="fas fa-user-times"></i> Elimina componente </a>';
						echo '<button class="btn btn-danger" onclick="return eliminac'.$m.'()"> <i class="fas fa-user-times"></i> Elimina componente</button>';

					}
					if($check_capo==0){
						//echo ' - <a class="btn btn-info" href="./squadre/add_capo_squadra.php?m='.$r['matricola_cf'].'&s='.$r['id'].'"> <i class="fas fa-user-check"></i>  Rendi capo squadra </a>';
						echo ' - <button class="btn btn-info" onclick="return addcs'.$m.'()"> <i class="fas fa-user-check"></i> Rendi capo squadra</button>';
					}
					echo "</div><br><hr>";
				
				?>
				
					<script type="text/javascript" >
						function eliminacs<?php echo $m;?>() {
							//alert('Test1');
							//var tel=document.getElementById('telsq<?php echo $m;?>').value;
							//var dataString='tel='+tel;
							$.ajax({
								type:"post",
								url:"./squadre/elimina_capo_squadra.php?s=<?php echo $id;?>&m=<?php echo $r['matricola_cf'];?>",
								//data:dataString,
								cache:false,
								/*success: function (html) {
									$('#msg').html(html);
								}*/
								//$('#componenti').load(document.URL +  ' #componenti');
							});
							$('#componenti').load(document.URL +  ' #componenti');
							return false;
						};
						
						function addcs<?php echo $m;?>() {
							//alert('Test1');
							//var tel=document.getElementById('telsq<?php echo $m;?>').value;
							//var dataString='tel='+tel;
							$.ajax({
								type:"post",
								url:"./squadre/add_capo_squadra.php?s=<?php echo $id;?>&m=<?php echo $r['matricola_cf'];?>",
								//data:dataString,
								cache:false,
								/*success: function (html) {
									$('#msg').html(html);
								}*/
								//$('#componenti').load(document.URL +  ' #componenti');
							});
							$('#componenti').load(document.URL +  ' #componenti');
							return false;
						};
						
						function eliminac<?php echo $m;?>() {
							//alert('Test1');
							//var tel=document.getElementById('telsq<?php echo $m;?>').value;
							//var dataString='tel='+tel;
							$.ajax({
								type:"post",
								url:"./squadre/elimina_componente.php?s=<?php echo $id;?>&m=<?php echo $r['matricola_cf'];?>",
								//data:dataString,
								cache:false,
								success: function (html) {
									//popolamento esterni
									let dropdown = $('#cf1');
									dropdown.selectpicker();
									dropdown.empty();
									dropdown.append('<option selected="true" disabled>Seleziona un utente esterno</option>');
									dropdown.prop('selectedIndex', 0);
									const url = './tables/griglia_squadre_esterni.php';
									// Populate dropdown with list of provinces
									$.getJSON(url, function (data) {
									  $.each(data, function (key, entry) {
										dropdown.append($('<option></option>').attr('value', entry.matricola_cf).text(entry.nome));
									  })
									  dropdown.selectpicker('refresh');
									});


									//popolamento dipendenti
									let dropdown2 = $('#cf2');
									dropdown2.selectpicker();
									dropdown2.empty();
									dropdown2.append('<option selected="true" disabled>Seleziona un dipendente</option>');
									dropdown2.prop('selectedIndex', 0);
									const url2 = './tables/griglia_squadre_dipendenti.php';
									// Populate dropdown with list of provinces
									$.getJSON(url2, function (data) {
									  $.each(data, function (key, entry) {
										dropdown2.append($('<option></option>').attr('value', entry.matricola_cf).text(entry.nome));
									  })
									  dropdown2.selectpicker('refresh');
									});
									//$('#msg').html(html);
								}
								//$('#componenti').load(document.URL +  ' #componenti');
							});
							$('#componenti').load(document.URL +  ' #componenti');
							return false;
						};
						
						
						
						</script>
				<?php
				}
				
				$i=0;
				$query_am="SELECT * FROM users.t_mail_squadre WHERE cod='".$id."' and matricola_cf IS NULL";
				$result_am = pg_query($conn, $query_am);
				while($r_am = pg_fetch_assoc($result_am)) {
					if($i==0){
						echo '<h4> Altre mail </h4>';
					} else {
						echo ' -  ';
					}
					$i=$i+1;
					echo $r_am['mail'];
					echo ' <a class="btn btn-danger" href="./squadre/delete_mail.php?m=\''.$r_am['mail'].'\'&s='.$id.'" > <i class="fa fa-times" aria-hidden="true"></i> </a> ';
					
				}
				?>
				
				
				</div>
				<br>
				<h2>Aggiungi componenti</h2> 
				</div>
            <!-- /.row -->
            <div class="row">
            <div class="col-md-6" id="esterni">
            <h4>Cerca utenti esterni - <?php echo $descrizione_profilo_squadra; ?> </h4>
			<?php    
			if ($profilo_ok >3 and $profilo_ok < 8) {
				echo "<i class=\"fas fa-user-slash fa-2x\"></i><br>L'utente con profilo ".$descrizione_profilo_squadra." non è abilitato all'utilizzo di utenti esterni";
			} else {
				//echo "cod_profilo:_squadra=".$cod_profilo_squadra."<br>";
				//echo (int)substr($cod_profilo_squadra,-1,1);
				//echo "<br>";
				/*if ($cod_profilo_squadra == "com_PC") {
					// vedo solo il Gruppo Genova
					$query2="SELECT * FROM users.v_utenti_esterni v 
					WHERE NOT EXISTS
						(SELECT matricola_cf FROM users.v_componenti_squadre s WHERE s.matricola_cf = v.cf and data_end is null) 
						AND id1 in (1,8)
						ORDER BY cognome";
				} else if (substr($cod_profilo_squadra,0,2)=='uo' OR (int)substr($cod_profilo_squadra,-1,1)>1){
					
					$query2="SELECT * FROM users.v_utenti_esterni v 
					WHERE NOT EXISTS
						(SELECT matricola_cf FROM users.v_componenti_squadre s WHERE s.matricola_cf = v.cf and data_end is null)
						and id1=".(int)substr($cod_profilo_squadra,-1)."
						ORDER BY cognome";
				}
					$result2 = pg_query($conn, $query2);*/
					//echo $query2;
				?>
						
				<!--form action="./squadre/add_squadra.php?s=<?php echo $id;?>" method="POST"-->
				<form>
				 <div class="form-group  ">
				  <label for="cf1">Utente esterno:</label> <font color="red">*</font>
					<select name="cf1" id="cf1" class="selectpicker show-tick form-control" data-dropup-auto="false" data-live-search="true" required="">
					</select>            
				 </div>

					<button  type="submit" class="btn btn-primary" onclick="return volontario()">
					<i class="fas fa-plus"></i>
					Aggiungi utente esterno selezionato a squadra</button>

				</form>
			<?php } ?>
              </div>
				<div class="col-md-6"  id="dipendenti">
					<h4>Cerca dipendenti comunali </h4> 
					
            <?php 
			if ($profilo_ok >=8) {
				echo "<i class=\"fas fa-user-slash fa-2x\"></i><br>L'utente con profilo ".$descrizione_profilo_squadra." non è abilitato all'utilizzo dei dipendenti comunali";
			} else {
            	
            ?>
							<!--form action="./squadre/add_squadra.php?s=<?php echo $id;?>" method="POST"-->
							<form>
			             <div class="form-group  ">
			              <label for="cf2">Dipendente:</label> <font color="red">*</font>
			              <select name="cf2" id="cf2" class="selectpicker show-tick form-control" data-dropup-auto="false" data-live-search="true" required="">
			              </select>            
			             </div>
							
							<button type="submit" class="btn btn-primary" onclick="return dipendente()">
							<i class="fas fa-plus"></i>
							Aggiungi dipendente selezionato a squadra </button>
							<!--button  type="submit" class="btn btn-primary"><i class="fas fa-plus"></i>
							Aggiungi dipendente selezionato a squadra</button-->
							<p id="msg"></p>
							</form>
				<?php } ?>							
				</div>
            
            </div>
            <!-- /.row -->
			<hr>
			<h2>Aggiungi mail</h2>
			<div class="row">
				<h4>Aggiungi nuova mail a squadra</h4>
			<form action="incarichi_interni/import_mail.php?s=<?php echo $id;?>" method="POST">
            <div class="col-md-12"> 
                <div class="form-group">
			        <label for="mail-input">Mail:</label> <font color="red">*</font>
			            <input class="form-control" type="mail" name="mailsq" id="mail-input"  required="">
			                
			    </div>
            </div>

			<div class="col-md-12"> 
				<button  type="submit" class="btn btn-primary">
				<i class="fas fa-at"></i>Aggiungi mail</button>
			</div>
			</form>

            
            </div>
			
			
            <hr>
            <div class="row">
            <a class="btn btn-info" href="./gestione_squadre.php"> <i class="fas fa-users"></i> Torna alla gestione squadre </a><br><br>
            </div>
            
            
    </div>
    <!-- /#wrapper -->

<?php 

require('./footer.php');

require('./req_bottom.php');


?>


<script type="text/javascript" >

//popolamento esterni
let dropdown = $('#cf1');
dropdown.selectpicker();
dropdown.empty();
dropdown.append('<option selected="true" disabled>Seleziona un utente esterno</option>');
dropdown.prop('selectedIndex', 0);
const url = './tables/griglia_squadre_esterni.php';
// Populate dropdown with list of provinces
$.getJSON(url, function (data) {
  $.each(data, function (key, entry) {
    dropdown.append($('<option></option>').attr('value', entry.matricola_cf).text(entry.nome));
  })
  dropdown.selectpicker('refresh');
});


//popolamento dipendenti
let dropdown2 = $('#cf2');
dropdown2.selectpicker();
dropdown2.empty();
dropdown2.append('<option selected="true" disabled>Seleziona un dipendente</option>');
dropdown2.prop('selectedIndex', 0);
const url2 = './tables/griglia_squadre_dipendenti.php';
// Populate dropdown with list of provinces
$.getJSON(url2, function (data) {
  $.each(data, function (key, entry) {
    dropdown2.append($('<option></option>').attr('value', entry.matricola_cf).text(entry.nome));
  })
  dropdown2.selectpicker('refresh');
});




function volontario() {
	var cf=document.getElementById('cf1').value;
	var dataString='cf='+cf;
	//$('#cf1').selectpicker();
	$.ajax({
		type:"post",
		url:"./squadre/add_squadra.php?s=<?php echo $id;?>",
		data:dataString,
		cache:false,
		success: function (html) {
			//$('#msg').html(html);
			dropdown.empty();
			dropdown.append('<option selected="true" disabled>Seleziona un utente esterno</option>');
			dropdown.prop('selectedIndex', 0);
			$.getJSON(url, function (data) {
			  $.each(data, function (key, entry) {
			    dropdown.append($('<option></option>').attr('value', entry.matricola_cf).text(entry.nome));
			  })
			  dropdown.selectpicker('refresh');
			});
			//dropdown.selectpicker('refresh');
		}
	});
	//alert('Test 1');	
	//$('#cf1').selectpicker('refresh');
	//alert('Test 2');
	$('#componenti').load(document.URL +  ' #componenti');
	return false;
}


function dipendente() {
	var cf=document.getElementById('cf2').value;
	var dataString='cf='+cf;
	$.ajax({
		type:"post",
		url:"./squadre/add_squadra.php?s=<?php echo $id;?>",
		data:dataString,
		cache:false,
		success: function (html) {
			//$('#msg').html(html);
			dropdown2.empty();
			dropdown2.append('<option selected="true" disabled>Seleziona un dipendente</option>');
			dropdown2.prop('selectedIndex', 0);
			$.getJSON(url2, function (data) {
			  $.each(data, function (key, entry) {
			    dropdown2.append($('<option></option>').attr('value', entry.matricola_cf).text(entry.nome));
			  })
			  dropdown2.selectpicker('refresh');
			});
			//dropdown2.selectpicker('refresh');
			
		}
		});
		$('#componenti').load(document.URL +  ' #componenti');
		return false;
}






</script>

    

</body>

</html>
