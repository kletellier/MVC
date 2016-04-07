<?php 
namespace Facades;

use GL\Core\Facades\Facade as BaseFacade;

class TestFacade extends BaseFacade {

	private static $instance;

	protected static function getInstance()
	{
		 if ( self::$instance === NULL) {
     
		$c = new self();
	    self::$instance = new \Application\Classes\Test;
	    }
	    return self::$instance;
	}
   	
   	/**
   	 * 
   	 * Return class instance or service name in string 
   	 */
    protected static function getFacadeAccessor() { return self::getInstance() ; }

}