<?php 

$subtitle="Dashboard o pagina iniziale";





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
//require('./conn.php');

require('./check_evento.php');
//require('./conteggi_dashboard.php');


/*if ($profilo_sistema == 10){
	header("location: ./index_nverde.php");
}*/
?>
    
</head>

<body data-spy="scroll" data-target=".navbar">

    <div id="wrapper" >

        <div id="navbar1">
<?php
require('navbar_up.php');
?>
</div>  

        <?php 
            require('./navbar_left.php'); 
            

        

        require('contatori_evento_embed.php');
		?>
		

        		
		
		
		
			
		


<?php 

require('./footer.php');

require('./req_bottom.php');


?> 

</body>

</html>
