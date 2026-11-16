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
<script src="../vendor/bootstrap-table/dist/extensions/print/bootstrap-table-print.min.js"></script>
<script src="../vendor/bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control.js"></script>
<script src="../vendor/bootstrap-select/dist/js/bootstrap-select.js"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.1.1/list.min.js"></script>

<!-- SheetJS (full) — prima di tableExport -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

<!-- tableExport (core) -->
<script src="https://cdn.jsdelivr.net/npm/tableexport.jquery.plugin@1.30.0/tableExport.min.js" integrity="sha256-JyCQ2nRcfgpZ59ajhyVPIcC7FLX3UUaWDX8dXJwLHWg=" crossorigin="anonymous"></script>

<!-- FileSaver (alcune build di tableExport non lo includono) -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js" integrity="sha512-Qlv6VSKh1gDKGoJbnyA5RMXYcvnpIqhO++MhIM2fStMcGT9i2T//tSwYFlcyoRRDcDZ+TYHpH8azBBCyhpSeqw==" crossorigin="anonymous"></script>

<!-- jsPDF + AutoTable per PDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js" integrity="sha512-qZvrmS2ekKPF2mSznTQsxqPgnpkI4DNTlrdUmTzrDgektczlKNRRhy5X5AAOnx5S09ydFYWWNSfcEqDTTHgtNA==" crossorigin="anonymous"></script>
<script>
  // Espone jsPDF in globale (necessario con la build UMD)
  window.jsPDF = window.jspdf.jsPDF;
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.29/jspdf.plugin.autotable.min.js" integrity="sha512-1/8DJLhOONj7obS4tw+A/2yb/cK9w5vWP+L4liQKYX/JeLZ/cqDuZfgDha4NK/kR/6b5IzHpS7/w80v4ED+8Mg==" crossorigin="anonymous"></script>

<!-- Estensione Export di Bootstrap Table (DEVE venire dopo tutte le dipendenze) -->
<script src="../vendor/bootstrap-table/dist/extensions/export/bootstrap-table-export.js"></script>

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