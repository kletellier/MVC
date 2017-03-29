<?php 
namespace GL\Core\Facades;

use GL\Core\Facades\Facade as BaseFacade;

class EventFacade extends BaseFacade {

	private static $instance;
    	
   	/**
   	 * 
   	 * Return class instance or service name in string 
   	 */
    protected static function getFacadeAccessor() { return "event" ; }

}