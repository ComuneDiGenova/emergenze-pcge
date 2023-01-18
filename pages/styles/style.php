<?php
header("Content-type: text/css; charset: UTF-8");
define('alert_color', $color_allerta);
?>

.panel-allerta {
border-color: <?php echo $color_allerta;
?>;
}

.panel-allerta>.panel-heading {
border-color: <?php echo $color_allerta; ?>;
color: white;
background-color: <?php echo $color_allerta; ?>;
}

.panel-allerta>a {
color: <?php echo $color_allerta; ?>;
}

.panel-allerta>a:hover {
color: #337ab7;
/* <?php echo $color_allerta; ?>;*/
}

.bootstrap-table .table>thead>tr>th {
vertical-align: center;
}
body {
margin-top: 100px;
}

.select,
#locale {
width: 100%;
}

.create_campaign {
margin-right: 10px;
}
.form-group.autoComplete:hover > .bootstrap-autocomplete.dropdown-menu, .form-group.autoComplete:focus >
.bootstrap-autocomplete.dropdown-menu {
display: inline;
}