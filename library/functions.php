<?php
 
if ( ! function_exists('ddump'))
{
	 /**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed
	 * @return void
	 */
	function ddump()
	{	 
		array_map(function($x) { var_dump($x); }, func_get_args()); die;	 
	}
}
