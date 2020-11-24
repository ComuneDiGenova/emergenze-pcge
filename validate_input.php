<?php

if (isset($_POST)){
		//PRINT OROGINAL $_POST
	/*
	echo 'qua ci arrivo <br>';
	foreach ($_POST as $key => $value) {
		echo '<p>'.$key.'</p>';
		echo '<p>'.$value.'</p>';
		$value = trim($value);
		$value = stripslashes($value);
		$value = htmlspecialchars($value);
		foreach($value as $k => $v){
			echo '<p>'.$k.'</p>';
			echo '<p>'.$v.'</p>';
			$v = trim($v);
			$v = stripslashes($v);
			$v = htmlspecialchars($v);
			echo '<hr />';
		}
	}
	echo "<br>";
	*/


	// qua valido i $_POST
	$_POST = array_map_deep($_POST, trim);
	$_POST = array_map_deep($_POST, stripslashes);
	$_POST = array_map_deep($_POST, htmlspecialchars);

	function array_map_deep( $value, $callback ) 
	{
		if ( is_array( $value ) ) {
			foreach ( $value as $index => $item ) {
					$value[ $index ] = array_map_deep( $item, $callback );
			}
		} elseif ( is_object( $value ) ) {
			$object_vars = get_object_vars( $value );
			foreach ( $object_vars as $property_name => $property_value ) {
					$value->$property_name = array_map_deep( $property_value, $callback );
			}
		} else {
			$value = call_user_func( $callback, $value );
		}
		return $value;
	}

	//PRINT NEW $_POST
	/*
	echo '<hr> anche qua ci arrivo <br>';

	 foreach ($_POST as $key => $value) {
	  echo '<p>'.$key.'</p>';
	  echo '<p>'.$value.'</p>';
	  foreach($value as $k => $v)
	  {
	  echo '<p>'.$k.'</p>';
	  echo '<p>'.$v.'</p>';
	  echo '<hr />';
	  }
	 }
	*/

	//exit;

	}
?>
