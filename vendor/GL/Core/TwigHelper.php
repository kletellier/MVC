<?php 
namespace GL\Core;

use GL\Core\Utils;

/**
 * Add function to Twig
 */
class TwigHelper extends \Twig_Extension
{
     

  public function __construct()
  {
       
  }
  
    public function getFunctions()
    {
        return array(
            'url'  => new \Twig_Function_Method($this, 'url'),            
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

    public function getName()
    {
        return 'TwigHelper';
    }

}