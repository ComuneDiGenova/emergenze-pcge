<?php 

$subtitle = "Attività sala emergenze";

$id = '';
$id = $_GET['id'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="roberto">

    <title>Gestione emergenze</title>

    <?php 
        require('./req.php');
        require(explode('emergenze-pcge', getcwd())[0] . 'emergenze-pcge/conn.php');
        require('./check_evento.php');

        // Redirect to access restriction page se non ha permessi
        if ($profilo_sistema > 3) {
            header("location: ./divieto_accesso.php");
        }
    ?>

    <!-- Link to CSS file -->
    <link rel="stylesheet" type="text/css" href="./styles/attivita_sala_emergenze.css">

</head>

<body>
    <div id="wrapper">
        <div id="navbar1">
            <?php
                require('navbar_up.php');
            ?>
        </div>
        
        <?php 
            require('./navbar_left.php');
        ?> 

        <div id="page-wrapper">
            <div class="row">
                <?php 
                    require('./attivita_sala_emergenze_embed.php'); 
                ?>
            </div>

            <div class="row">
            <br></br>
            </div>

        <div class="container mt-4">
            <div class="row">
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <h4>Attivazione numero verde: 
                            <?php if ($contatore_nverde > 0) { ?>
                                <i class="text-success">Attivo</i>
                            <?php } else { ?>
                                <i class="text-danger">Non attivo</i>
                            <?php } ?>
                        </h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <br></br>
        </div>


        <?php 
            require('./footer.php');
            require('./req_bottom.php');
        ?>

        <script src="./scripts/attivita_sala_emergenze.js"></script>
    </body>
</html>
