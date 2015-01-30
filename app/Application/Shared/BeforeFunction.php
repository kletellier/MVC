<?php 
namespace Application\Shared;   

use Symfony\Component\DependencyInjection\ContainerInterface; 


class BeforeFunction implements \GL\Core\BeforeFunctionInterface
{ 	
    protected $_container;		 

    public function __construct(ContainerInterface $container = null)
    {
       $this->_container = $container;		    
    }

    public function execute()
    {
        
        return true;
    }
}