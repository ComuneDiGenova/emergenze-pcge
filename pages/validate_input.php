<?php

$_POST = array_map_deep($_POST, pg_escape_string);
<<<<<<< HEAD

=======
>>>>>>> feature/coc-senza-bollettino
$_GET = array_map_deep($_GET, pg_escape_string);

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

?>
