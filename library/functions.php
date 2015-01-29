<?php
use Symfony\Component\VarDumper\VarDumper;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;

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

if ( ! function_exists('sdump'))
{
	  /**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed
	 * @return void
	 */
	function sdump()
	{	 
		array_map(function($x) { dump($x); }, func_get_args()); die;	 
	}
}


