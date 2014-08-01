<?php 
namespace GL\Core;

use GL\Core\Utils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Add function to Twig
 */
class TwigHelper extends \Twig_Extension
{
  protected $container;   

  public function __construct(ContainerInterface $container = null)
  {
       $this->container = $container;
  }
  
    public function getFunctions()
    {
        return array(
            'url'  => new \Twig_Function_Method($this, 'url'), 
            'crsf' => new \Twig_Function_Method($this,'crsf'),
        );
    }
	
    public function getFilters()
    {
        return array(            
			'frenchdate' => new \Twig_Filter_Method(  $this, 'getFrenchDate'),
        );
    }
	
    /**
     * Function for returning date in french format like Lundi 12 Mai 1999 23:12
     * @param \Datetime $datetime date to convert
     * @param type $lang Conversion language default french
     * @param type $pattern default pattern, french as default value
     * @return string
     */
    public function getFrenchDate(\Datetime $datetime, $lang = 'fr_FR', $pattern = 'E dd MMM yyyy HH:mm ')
    {
            $formatter = new \IntlDateFormatter($lang, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
            $formatter->setPattern($pattern);
            return $formatter->format($datetime);
    }
        
    /**
     * Convert relative url to absolute url
     * 
     * @param string $partialurl relative url
     * @return string absolute url (use BASE_PATH define in config.php)
     */
    public function url($partialurl)
    {
        return Utils::url($partialurl);        
    }
    
    /**
     * Get Actual Token
     * 
     * @return string Actual Crsf Token
     */
    public function crsf()
    {
        $token = "";
        if($this->container!=null)
        {
            $crsf = $this->container->get('crsf');
            if($crsf!=null)
            {
                $token = $crsf->GetToken();
                if($token==null || $token=="")
                {
                    $token = $crsf->InitCrsf();
                }                 
            }
        }
        return $token;
    }
    
    public function getName()
    {
        return 'TwigHelper';
    }

}