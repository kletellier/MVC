<?php 
namespace Application\Shared;   

use Symfony\Component\DependencyInjection\ContainerInterface; 

/**
 * Class to add global parameters to view parameters
 * Called by controller before rendering
 */
class GlobalFunction 
{ 	
    protected $_vals;
    protected $_container;		 

    public function __construct(array $array = array(),ContainerInterface $container = null)
    {
       $this->_vals = $array;
       $this->_container = $container;		    
    }

    public function execute()
    {
        // Add global variables to view
        //$this->_vals['test'] = "test";

        // Warning don't forget to return the array, it contains all your parameters sent to the view.
        return $this->_vals;
    }
}