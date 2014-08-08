<?php 
namespace Application\Shared;
  
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add function to Twig
 */
class SharedTwigHelper extends \Twig_Extension
{
  protected $container;   

  public function __construct(ContainerInterface $container = null)
  {
       $this->container = $container;
  }
  
    public function getFunctions()
    {
        return array(
            'test'  => new \Twig_Function_Method($this, 'test'), 
            
        );
    }
	
    public function getFilters()
    {
        return array(            
			 
        );
    }
	
    
    
    public function test()
    {
        return "test__";        
    }
    
    
    public function getName()
    {
        return 'SharedTwigHelper';
    }

}