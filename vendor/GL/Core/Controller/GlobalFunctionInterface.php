<?php 
namespace GL\Core\Controller;   

use Symfony\Component\DependencyInjection\ContainerInterface; 

interface GlobalFunctionInterface 
{ 	
    	 
      /**
     * Constructor
     * @param array $array parameters array provides by controller action
     * @param ContainerInterface $container service container
     * @return void
     */
    function __construct(array $array,ContainerInterface $container);
    
    /**
     *  Inject variable into provided parameters array
     * @return array MUST always return parameters array 
     */
    public function execute();
    
}