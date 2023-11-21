<?php
//pg_close($conn);
$subtitle2 = str_replace("'", "\'", str_replace(' ', '_', $subtitle));
//echo $subtitle2;
?>

</div>
<!-- /#wrapper -->

<!-- jQuery -->
<!--script src="../vendor/jquery/jquery.min.js"></script-->

<!-- Bootstrap Core JavaScript -->
<script src="../vendor/bootstrap/js/bootstrap.min.js"></script>

<!-- Bootstrap Plugins -->
<script src="../vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.js"></script>
<script src="../vendor/bootstrap-table/dist/bootstrap-table.min.js"></script>
<script src="../vendor/bootstrap-table/dist/extensions/export/bootstrap-table-export.js"></script>
<script src="../vendor/bootstrap-table/dist/extensions/print/bootstrap-table-print.min.js"></script>
<script src="../vendor/bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control.js"></script>
<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.9.0/moment.min.js" integrity="sha512-l7jogMOI6ZWZJEY7lREjFdQum46y2+kpp/mnbJx7O+izymO9eGjL6Y4o7cEJNBdouhVHpti2Wd79Q6aIjPwxtQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker-standalone.css" integrity="sha512-wT6IDHpm/cyeR3ASxyJSkBHYt9oAvmL7iqbDNcAScLrFQ9yvmDYGPZm01skZ5+n23oKrJFoYgNrlSqLaoHQG9w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker-standalone.min.css" integrity="sha512-L0/PNISezIYAoqFXBGP9EJ4qLH8XF356+Lo92vzloQqk7HUpZ4FN1x1dUOnsUAUjHTSxXxeaD0HXfrANhtJOEA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.css" integrity="sha512-mQ8Fj7epKOfW0M7CwuuxdPtzpmtIB5rI4rl76MSd3mm5dCYBKjzPk7EU/2buhPMs0KmC6YOPR/MQlQwpkdNcpQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/css/bootstrap-datetimepicker.min.css" integrity="sha512-WWc9iSr5tHo+AliwUnAQN1RfGK9AnpiOFbmboA0A0VJeooe69YR2rLgHw13KxF1bOSLmke+SNnLWxmZd8RTESQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.37/js/bootstrap-datetimepicker.min.js" integrity="sha512-Y+0b10RbVUTf3Mi0EgJue0FoheNzentTMMIE2OreNbqnUPNbQj8zmjK3fs5D2WhQeGWIem2G2UkKjAL/bJ/UXQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.1.1/list.min.js"></script>
<script src="//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js"></script>

<!-- Leaflet JavaScript -->
<script src="../vendor/leaflet/leaflet.js"></script>

<!-- Metis Menu Plugin JavaScript -->
<script src="../vendor/metisMenu/metisMenu.min.js"></script>

<!-- Custom Theme JavaScript -->
<script src="../dist/js/sb-admin-2.js"></script>

<script type="text/javascript">

    //**************************************************************
    //Automatic refresh page in case of inactivity every 10 minutes
    var time = new Date().getTime();
    //$(document.body).bind("mousemove keypress", function(e) {
    $(document.body).bind("click keypress wheel", function (e) {
        //alert('Timer aggiornato');
        time = new Date().getTime();
    });

    function refresh() {
        if (new Date().getTime() - time >= 600000)
            window.location.reload(true);
        else
            setTimeout(refresh, 30000);
    }

    setTimeout(refresh, 30000);


    <?php
    if ($profilo_sistema > 0) {
        ?>
        // reload navbar ogni 30''
        $(document).ready(function () {
            var timeout = setInterval(reloadChat, 30000);
        });
        <?php
    }
    ?>


    function reloadChat() {
        //$('#navbar_emergenze').load('navbar_up.php?r=true&&s\'<?php echo $subtitle; ?>\'');
        <?php
        if (basename($_SERVER['PHP_SELF']) == 'index.php') {
            ?>
            $('#navbar1').load('navbar_up.php?r=true&i=true&s=<?php echo $subtitle2; ?>');
            <?php
        }
        else {
            ?>
            $('#navbar1').load('navbar_up.php?r=true&i=false&s=<?php echo $subtitle2; ?>');
            <?php
        }
        ?>

    }




    var onResize = function () {
        // apply dynamic padding at the top of the body according to the fixed navbar height
        $("body").css("padding-top", $(".navbar-fixed-top").height());
    };

    // attach the function to the window resize event
    $(window).resize(onResize);

    // call it also when the page is ready after load or reload
    $(function () {
        onResize();
    });



    //////////////////////////////////////////////////////////////
    //sidebar scrollable
    var topNavBar = 50;
    var footer = 48;
    var height = $(window).height();
    //$('.sidebar').css('height', (height - (topNavBar+footer)));
    $('.sidebar').css('height', (height - (topNavBar)));


    $(window).resize(function () {
        var height = $(window).height();
        //$('.sidebar').css('height', (height - (topNavBar+footer)));
        $('.sidebar').css('height', (height - (topNavBar)));
    });
    //////////////////////////////////////////////////////////////



    // prevent multiple submit
    $("body").on("submit", "form", function () {
        $(this).submit(function () {
            return false;
        });
        return true;
    });

    <?php
    if ($privacy == 'f') {
        ?>
        $('#privacy_modal').modal('show');
        <?php
    }
    ?>


    // funzione per stampa al volo 
    function printDiv(divName) {
        var printContents = document.getElementById(divName).innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    }



    function printClass(className) {
        //it is an array so i using only the first element
        var printContents = document.getElementsByClassName(className)[0].innerHTML;
        var originalContents = document.body.innerHTML;

        document.body.innerHTML = printContents;

        window.print();

        document.body.innerHTML = originalContents;
    }
</script>





<?php
?>