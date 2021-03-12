<?php 

$subtitle="Contatori"

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
                    <h1 class="page-header"> <i class="fas fa-tachometer-alt"></i> Contatori generali</h1>
                </div>
                <!-- /.col-lg-12 -->
            </div>
            <!-- /.row -->
            
            <br><br>
            <div class="row">
            
            <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
            <div class="panel-body">
            <h1>
            <div id="counter">
            <?php 
            $query='select count(e.id) as test from eventi.t_eventi e
            join eventi.join_tipo_evento t on t.id_evento=e.id 
            where e.id >= 55 and t.id_tipo_evento not in (8,9);';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['test'];            
            }
            ?>
            </div>
            eventi
            </h1>
            </div>
            </div>
            </div>


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
            <div class="panel-body">
            <h1>
            <div id="counter2">
            <?php 
            $query='select count(e.id) as test from eventi.t_eventi e
            join eventi.join_tipo_evento t on t.id_evento=e.id 
            where e.id >= 55 and t.id_tipo_evento in (8,9);';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['test'];            
            }
            ?>
            </div> eventi mensili
            </h1>
            </div>
            </div>
            </div>


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
            <div class="panel-body">
            <h1>
            <div id="counter3">
            <?php 
            $query='select count(id) from segnalazioni.t_segnalazioni where id_evento >= 55; ';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            
            </div> segnalazioni
            </h1>
            </div>
            </div>
            </div>


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-danger">
            <div class="panel-body">
            <h1>
            <div id="counter4">
            <?php 
            $query='select count(id) from segnalazioni.t_richieste_nverde where id_evento >= 55  ';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> richieste generiche a numero verde
            </h1>
            </div>
            </div>
            </div>


            </div>
            <hr>
            <div class="row">

            <div class="col-lg-3 col-md-6">
            <div class="panel panel-danger">
            <div class="panel-body">
            <h1>
            <div id="counter5">
            <?php 
            $query='select count(*) from varie.dipendenti;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> dipendenti comunali
            </h1>
            </div>
            </div>
            </div>

            
            <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
            <div class="panel-body">
            <h1>
            <div id="counter6">
            <?php 
            $query='select count(cf) from users.utenti_esterni ue;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> utenti esterni registrati
            </h1>
            </div>
            </div>
            </div>


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-default">
            <div class="panel-body">
            <h1>
            <div id="counter7">
            <?php 
            $query='select count(matricola_cf) from users.utenti_sistema;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> utenti con accesso al sistema
            </h1>
            </div>
            </div>
            </div>


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
            <div class="panel-body">
            <h1>
            <div id="counter8">
            <?php 
            $query='select count(id) from users.t_squadre ts where id_evento is null or id_evento >55';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> squadre registrate
            </h1>
            </div>
            </div>
            </div>

            </div>
            <hr>
            <div class="row">

            


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-danger">
            <div class="panel-body">
            <h1>
            <div id="counter9">
            <?php 
            $query='select count(*) from segnalazioni.t_incarichi tii 
            where data_ora_invio > (select data_ora_inizio_evento from eventi.t_eventi te where id =55) ;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> incarichi
            </h1>
            </div>
            </div>
            </div>

            <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
            <div class="panel-body">
            <h1>
            <div id="counter10">
            <?php 
            $query='select count(*) from segnalazioni.t_incarichi_interni tii 
            where data_ora_invio > (select data_ora_inizio_evento from eventi.t_eventi te where id =55) ;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> incarichi interni
            </h1>
            </div>
            </div>
            </div>


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-info">
            <div class="panel-body">
            <h1>
            <div id="counter11">
            <?php 
            $query='select count(*) from segnalazioni.t_sopralluoghi tii 
            where data_ora_invio > (select data_ora_inizio_evento from eventi.t_eventi te where id =55) ;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> presidi
            </h1>
            </div>
            </div>
            </div>

            <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
            <div class="panel-body">
            <h1>
            <div id="counter12">
            <?php 
            $query='select count(*) from segnalazioni.t_sopralluoghi_mobili tii 
            where data_ora_invio > (select data_ora_inizio_evento from eventi.t_eventi te where id =55) ;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> presidi mobili
            </h1>
            </div>
            </div>
            </div>

            </div>
            <hr>
            <div class="row">


            <div class="col-lg-3 col-md-6">
            <div class="panel panel-danger">
            <div class="panel-body">
            <h1>
            <div id="counter13">
            <?php 
            $query='select count(*) from segnalazioni.t_provvedimenti_cautelari tii 
            where data_ora_invio > (select data_ora_inizio_evento from eventi.t_eventi te where id =55) ;';
            //$result = pg_prepare($conn, myquery, $query);
            $result = pg_query($conn, $query);
            while($r = pg_fetch_assoc($result)) { 
                echo $r['count'];            
            }
            ?>
            </div> provvedimenti cautelari
            </h1>
            </div>
            </div>
            </div>


            <!--script>
            $({countNum: 1}).animate({countNum: $('#counter').text()}, {
            duration: 1000,
            easing:'linear',
            step: function() {
                $('#counter').text(Math.floor(this.countNum));
            },
            complete: function() {
                $('#counter').text(this.countNum);
                //alert('finished');
            }
            });
            </script-->



            <?php
            $x = 1;

            while($x <= 14) {
            ?>
            <script>
            $({countNum: 1}).animate({countNum: $('#counter<?php echo $x;?>').text()}, {
            duration: 1000,
            easing:'linear',
            step: function() {
                $('#counter<?php echo $x;?>').text(Math.floor(this.countNum));
            },
            complete: function() {
                $('#counter<?php echo $x;?>').text(this.countNum);
                //alert('finished');
            }
            });
            </script>
            <?php
            $x++;
            }
            ?>
            


            <!--script>
            $({countNum: 1}).animate({countNum: $('#counter2').text()}, {
            duration: 1000,
            easing:'linear',
            step: function() {
                $('#counter2').text(Math.floor(this.countNum));
            },
            complete: function() {
                $('#counter2').text(this.countNum);
                //alert('finished');
            }
            });
            </script-->
            
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
