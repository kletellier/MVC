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
		array_map(function($x) { echo '<pre>';  var_dump($x) ; echo '</pre>'; }, func_get_args()); die;	 
	}
}

if ( ! function_exists('gdump'))
{
	  /**
	 * Dump the passed variables and end the script.
	 *
	 * @param  mixed
	 * @return void
	 */
	function gdump()
	{	 
		array_map(function($x) {    \GL\Core\Debug\Debug::dump($x) ;   }, func_get_args()); die;	 
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


if( !function_exists('tpl'))
{
	/**
	 * Return path for a template
	 * @param  string $p relative path
	 * @return string absolute path
	 */
	function tpl($p)
	{
		return TEMPLATEPATH . DS . $p;
	}
}