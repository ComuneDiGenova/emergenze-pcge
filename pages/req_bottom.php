<?php 
//pg_close($conn);
$subtitle2=str_replace("'","\'",str_replace(' ','_',$subtitle));
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
	 <script src="../vendor/bootstrap-table/dist/extensions/export/bootstrap-table-export.js" ></script>
	 <script src="../vendor/bootstrap-select/dist/js/bootstrap-select.js"></script>
	 
	 
	 <script src="https://cdnjs.cloudflare.com/ajax/libs/list.js/1.1.1/list.min.js"></script>
	 
	 
    <script src="//rawgit.com/hhurz/tableExport.jquery.plugin/master/tableExport.js"></script>

	<!-- Leaflet JavaScript -->
	<script src="../vendor/leaflet/leaflet.js"></script>




    <!-- Metis Menu Plugin JavaScript -->
    <script src="../vendor/metisMenu/metisMenu.min.js"></script>


    <!-- Morris Charts JavaScript -->
    <!--script src="../vendor/raphael/raphael.min.js"></script>
    <script src="../vendor/morrisjs/morris.min.js"></script>
    <script src="../data/morris-data.js"></script-->

    <!-- Custom Theme JavaScript -->
    <script src="../dist/js/sb-admin-2.js"></script>
    
    <!-- Library for FORM persistency http://sisyphus-js.herokuapp.com/-->
	<!--script src="../vendor/sisyphus/sisyphus.min.js"></script-->

	<!--script type="text/javascript">
	// Here we'll persist the form's data into localStorage on
	// every keystroke
	$( function() {
	//$( "#basic_form" ).sisyphus();
	// or you can persist all forms data at one command
	 $( "form" ).sisyphus();
	} );

</script-->


<script type="text/javascript">

//**************************************************************
//Automatic refresh page in case of inactivity every 10 minutes
  var time = new Date().getTime();
  //$(document.body).bind("mousemove keypress", function(e) {
  $(document.body).bind("click keypress wheel", function(e) {
  	  //alert('Timer aggiornato');
      time = new Date().getTime();
  });

  function refresh() {
      if(new Date().getTime() - time >= 600000) 
          window.location.reload(true);
      else 
          setTimeout(refresh, 30000);
  }

  setTimeout(refresh, 30000);
  
  
  
// reload navbar ogni 30''
$(document).ready(function(){
  var timeout = setInterval(reloadChat, 30000); 
});

   
function reloadChat () {
     //$('#navbar_emergenze').load('navbar_up.php?r=true&&s\'<?php echo $subtitle;?>\'');
	 $('#navbar_emergenze').load('navbar_up.php?r=true&s=<?php echo $subtitle2;?>');
}

/*function loadnavbar(){
    $('#navbar_emergenze').load('./check_evento.php'),function () {
         $(this).unwrap();
    });
}

loadnavbar(); // This will run on page load
setInterval(function(){
    loadnavbar() // this will run after every 5 seconds
}, 100000);*/


 
 
//funge, ma sembra mandare in crisi il server... 
 
/*  var time = new Date().getTime();
	$(document.body).bind("click keypress wheel", function () {
		  //alert('Timer aggiornato');
    		time = new Date().getTime();
	});

setInterval(function() {
    if (new Date().getTime() - time >= 300000) {
        window.location.reload(true);
    }
}, 1000);*/
  



var onResize = function() {
  // apply dynamic padding at the top of the body according to the fixed navbar height
  $("body").css("padding-top", $(".navbar-fixed-top").height());
};

// attach the function to the window resize event
$(window).resize(onResize);

// call it also when the page is ready after load or reload
$(function() {
  onResize();
});



//////////////////////////////////////////////////////////////
//sidebar scrollable
var topNavBar = 50;
var footer = 48;
var height = $(window).height();
//$('.sidebar').css('height', (height - (topNavBar+footer)));
$('.sidebar').css('height', (height - (topNavBar)));


$(window).resize(function(){
    var height = $(window).height();
    //$('.sidebar').css('height', (height - (topNavBar+footer)));
    $('.sidebar').css('height', (height - (topNavBar)));
});
//////////////////////////////////////////////////////////////



// prevent multiple submit
$("body").on("submit", "form", function() {
    $(this).submit(function() {
        return false;
    });
    return true;
});

<?php
if ($privacy=='f'){
	?>
		$('#privacy_modal').modal('show'); 
	<?php
}
?>



</script>





<?php
?>
