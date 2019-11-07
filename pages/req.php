<?php 

//$page = $_SERVER['PHP_SELF'];
//$sec = "60";


function integerToRoman($integer)
{
 // Convert the integer into an integer (just to make sure)
 $integer = intval($integer);
 $result = '';
 
 // Create a lookup array that contains all of the Roman numerals.
 $lookup = array('M' => 1000,
 'CM' => 900,
 'D' => 500,
 'CD' => 400,
 'C' => 100,
 'XC' => 90,
 'L' => 50,
 'XL' => 40,
 'X' => 10,
 'IX' => 9,
 'V' => 5,
 'IV' => 4,
 'I' => 1);
 
 foreach($lookup as $roman => $value){
  // Determine the number of matches
  $matches = intval($integer/$value);
 
  // Add the same number of characters to the string
  $result .= str_repeat($roman,$matches);
 
  // Set the integer to be the remainder of the integer and the value
  $integer = $integer % $value;
 }
 
 // The Roman numeral should be built, return it
 return $result;
}

?>


<meta http-equiv="Cache-control" content="public">

<link rel="icon" href="favicon.ico" type="image/x-icon"/>
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"/>



<!-- Bootstrap Core CSS -->
    <link href="../vendor/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Bootstrap Plugins -->
    <link href="../vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.css" rel="stylesheet">
    <link href="../vendor/bootstrap-table/dist/bootstrap-table.min.css" rel="stylesheet">
    <link href="../vendor/bootstrap-select/dist/css/bootstrap-select.css" rel="stylesheet">
	

    <!-- Leaflet CSS -->
    <link href="../vendor/leaflet/leaflet.css" rel="stylesheet">
     <link rel="stylesheet" href="l_map/css/leaflet-measure.css">

    <!-- MetisMenu CSS -->
    <link href="../vendor/metisMenu/metisMenu.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="../dist/css/sb-admin-2.css" rel="stylesheet">

    <!-- Morris Charts CSS -->
    <link href="../vendor/morrisjs/morris.css" rel="stylesheet">

    <!-- Custom Fonts -->
    <link href="../vendor/fontawesome-free-5.2.0-web/css/all.css" rel="stylesheet" type="text/css">
	
	<link href="../vendor/font-awesome-animation/dist/font-awesome-animation.css" rel="stylesheet" type="text/css">
    
    <style type="text/css">
    #wrapper { 
    	/*padding-top:50px;*/
		padding-top: $('.navbar').height()
    }

	
	.sidebar{
		overflow-y: scroll;
		position: fixed;
		margin-top: 0px;
		z-index:1;
	}
    </style>
    
    

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

    <!-- jQuery -->
    <script src="../vendor/jquery/jquery.min.js"></script>
	
	<!-- GRAFICI d3js -->
	<script src="https://d3js.org/d3.v4.min.js"></script>

<?php
?>
