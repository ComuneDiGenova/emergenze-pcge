<?php 

$subtitle="Credits"

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
                    <h1 class="page-header">Credits</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->

            
            <br><br>
            <div class="row">
<span class="copyright">
Il presente applicativo realizzato da <a href="http://www.gter.it" target="_blank">Gter srl</a> per conto della Protezione Civile del Comune di Genova. In caso di problemi contattare l'<a href="mailto:adminemergenzepc@comune.genova.it">amministratore di sistema</a>
<br>
<img class="nav nav-second-level" src="../img/pc_ge.png" width="15%" alt="">
<br>
<br>
Progetto finanziato con i fondi PON Metro 2014-2020.
<br>
<a href="http://www.ponmetro.it/" target="_blank"><img src="../img/pon_metro/Barra_loghi.png" width="50%" alt=""></a>

<br> <br>
L'appplicativo web è stato realizzato da Gter a partire da <i>SB Admin 2</i>, a free to use, open source Bootstrap theme created by <a href="http://startbootstrap.com" target="_blank"> Start Bootstrap</a>
Il codice sorgente dell'intero applicativo è disponibile su GitHub al seguente <a href="https://github.com/gtergeomatica/emergenze-pcge" target="_blank" ><i class="fab fa-github"></i>link</a>.
<br>

	</span>
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
