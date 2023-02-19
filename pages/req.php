<?php
//$page = $_SERVER['PHP_SELF'];
//$sec = "60";
// Define ->>> constant_name, value, case_insensitive
const LOCAL_DEV = true;
$directory_path = '';
$html_stylesheet = ' rel="stylesheet">';
if (LOCAL_DEV) {
	$directory_path = '../';
}
echo "<!-- directory_path is: " . $directory_path . " -->";

function integerToRoman($integer) {
	// Convert the integer into an integer (just to make sure)
	$integer = intval($integer);
	$result = '';

	// Create a lookup array that contains all of the Roman numerals.
	$lookup = array('M' => 1000,
		'CM' => 900,
		'D'  => 500,
		'CD' => 400,
		'C'  => 100,
		'XC' => 90,
		'L'  => 50,
		'XL' => 40,
		'X'  => 10,
		'IX' => 9,
		'V'  => 5,
		'IV' => 4,
		'I'  => 1);

	foreach ($lookup as $roman => $value) {
		// Determine the number of matches
		$matches = intval($integer / $value);

		// Add the same number of characters to the string
		$result .= str_repeat($roman, $matches);

		// Set the integer to be the remainder of the integer and the value
		$integer = $integer % $value;
	}

	// The Roman numeral should be built, return it
	return $result;
}

?>

<meta http-equiv="Cache-control" content="public">
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<style type="text/css">
	#wrapper {
		/*padding-top:50px;*/
		padding-top: $(
			'.navbar').height()
	}


	.sidebar {
		overflow-y: scroll;
		position: fixed;
		margin-top: 0px;
		z-index: 1;
	}

	.panel-allerta {
		border-color:
			<?php echo $color_allerta; ?>
		;
	}

	.panel-allerta>.panel-heading {
		border-color:
			<?php echo $color_allerta; ?>
		;
		color: white;
		background-color:
			<?php echo $color_allerta; ?>
		;
	}

	.panel-allerta>a {
		color:
			<?php echo $color_allerta; ?>
		;
	}

	.panel-allerta>a:hover {
		color: #337ab7;
		/* <?php echo $color_allerta; ?>
		;
		*/
	}


	.dot {
		height: 25px;
		width: 25px;
		/*background-color: #bbb;*/
		border-radius: 50%;
		display: inline-block;
	}


	.fa {
		-webkit-print-color-adjust: exact;
	}

	.fas {
		-webkit-print-color-adjust: exact;
	}



	@media print {

		/* commentata riga 191 del file bootstrap.css per consentire la stampa dei colori*/
		.fa {
			-webkit-print-color-adjust: exact;
		}

		.fas {
			-webkit-print-color-adjust: exact;
		}

		p.bodyText {
			font-family: georgia,
				times,
				serif;
			-webkit-print-color-adjust: exact;
			color-adjust: exact;
		}

		.rows-print-as-pages .row {
			page-break-before: auto;
		}

		.btn {
			display: none;
		}


		table,
		table tr td,
		table tr th {
			page-break-inside: avoid;
		}

		.collapse {
			display: block !important;
			height: auto !important;
		}


		#break {
			page-break-before: always;
		}

		.fa-inverse {
			color: #fff !important;
		}

		.noprint {
			display: none
		}


	}
</style>
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
	<![endif]-->

<?php
//? CSS plugins 
echo '		<!-- Bootstrap Core CSS -->';
echo '<link href=' . $directory_path . '../vendor/bootstrap/css/bootstrap.css ' . $html_stylesheet;
echo '		<!-- Bootstrap Plugins -->';
echo '<link href=' . $directory_path . '../vendor/bootstrap-datepicker/dist/css/bootstrap-datepicker.css' . $html_stylesheet;
echo '<link href=' . $directory_path . '../vendor/bootstrap-table/dist/bootstrap-table.min.css' . $html_stylesheet;
echo '<link href=' . $directory_path . '../vendor/bootstrap-table/dist/extensions/filter-control/bootstrap-table-filter-control.css' . $html_stylesheet;
echo '<link href=' . $directory_path . '../vendor/bootstrap-select/dist/css/bootstrap-select.css' . $html_stylesheet;
echo '		<!-- Leaflet CSS -->';
echo '<link href=' . $directory_path . '../vendor/leaflet/leaflet.css' . $html_stylesheet;
echo '<link href=' . $directory_path . 'l_map/css/leaflet-measure.css' . $html_stylesheet;
echo '		<!-- MetisMenu CSS -->';
echo '<link href=' . $directory_path . '../vendor/metisMenu/metisMenu.min.css' . $html_stylesheet;
echo '		<!-- Custom CSS -->';
echo '<link href=' . $directory_path . '../dist/css/sb-admin-2.css' . $html_stylesheet;
echo '		<!-- Morris Charts CSS -->';
echo '<link href=' . $directory_path . '../vendor/morrisjs/morris.css' . $html_stylesheet;
echo '		<!--link href=../vendor/highcharts/code/css/highcharts.css' . $html_stylesheet;
echo '		<!-- Custom Fonts -->';
echo '<link href=' . $directory_path . '../vendor/fontawesome-free-5.13.0-web/css/all.css' . ' type=text/css ' . $html_stylesheet;
echo '<link href=' . $directory_path . '../vendor/font-awesome-animation/dist/font-awesome-animation.css' . ' type=text/css ' . $html_stylesheet;
?>
<?php
//? JS Plugins
echo '		 <!-- jQuery -->';
echo '<script src=' . $directory_path . '../vendor/jquery/jquery.min.js ></script>';
echo '		<!-- Bootstrap js -->';
echo '<script src=' . $directory_path . '../vendor/bootstrap/js/bootstrap.min.js defer></script>';
echo '<!-- Bootstrap table -->';
echo '<script src=' . $directory_path . '../vendor/bootstrap-table/dist/bootstrap-table.js defer></script>';
echo '		<!-- GRAFICI d3js -->';
echo '<script src=https://d3js.org/d3.v4.min.js defer></script>';
echo '		<!-- GRAFICI highcart -->';
echo '<script src=' . $directory_path . '../vendor/highcharts/code/highcharts.js defer></script>';
echo '<script src=' . $directory_path . '../vendor/highcharts/code/modules/stock.js defer></script>';
echo '<script src=' . $directory_path . '../vendor/highcharts/code/modules/data.js defer></script>';
echo '<script src=' . $directory_path . '../vendor/highcharts/code/modules/exporting.js defer></script>';
echo '<script src=' . $directory_path . '../vendor/highcharts/code/modules/export-data.js defer></script>';
; ?>