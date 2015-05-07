<?php 
namespace GL\Core\Controller;   

use Symfony\Component\DependencyInjection\ContainerInterface; 

interface FilterResponseInterface 
{   
         
      /**
     * Constructor
     * @param Response $response Response object
     * @param ContainerInterface $container service container
     * @return void
     */
    function __construct(\Symfony\Component\HttpFoundation\Response $response,\Symfony\Component\DependencyInjection\Container $container);
    
    /**
     *   
     * @return Response MUST always return Response object
     */
    public function execute();
    
}