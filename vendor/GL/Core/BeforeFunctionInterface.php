<?php 
namespace GL\Core;   

use Symfony\Component\DependencyInjection\ContainerInterface; 

interface BeforeFunctionInterface 
{ 	
    	 
      /**
     * Constructor
     * @param ContainerInterface $container service container
     * @return void
     */
    function __construct(ContainerInterface $container);
    
    /**
     *  execute before function
     * @return object 
     */
    public function execute();
    
}