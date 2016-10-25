<?php 

namespace GL\Core\Routing;

/**
 * Class to extract route from array given by Symfony Routing component
 */
class RouteParser {

    private $tableau;
    private $controller;
    private $action;
    private $name;
    private $pattern;
    private $defaults;
    private $methods;

    /**
     * Constructor
     * @param array $tableau route array given by Symfony Routing Component
     * @param string $nom route name
     */
    public function __construct($tableau,$nom) {
        $this->tableau = $tableau;		
        $this->controller = '';
        $this->action = '';
        $this->name = $nom;
	    $this->pattern = '';
        $this->defaults = null;
        $this->methods = array();
    }
    
    /**
     * Parse route and return boolean if route is valid.
     * 
     * @return boolean
     */
    public function parse() 
    {
        $ret = false;
        if(sizeof($this->tableau)>=3)
        {
            $this->pattern = $this->tableau["pattern"];
            $this->controller = $this->tableau["controller"];
            $this->action = $this->tableau["action"];
            if(isset($this->tableau["defaults"]))
            {
                 $this->defaults = $this->tableau["defaults"];                             
            }     
            if(isset($this->tableau["methods"]))  
            {
                $this->methods = $this->tableau["methods"];   
            }                 
            $ret = true;
        }        
        return $ret;
    }
    
    /**
     * return default parameters array
     * 
     * @return array return default parameters array
     */
    public function getArrayParams()
    {
        // fonction qui rajoute les valeurs d'options par défaut
        $arr = array('controller'=>$this->controller);
        $arr = array_add($arr, 'action', $this->action);
        if(isset($this->defaults))
        {
            foreach($this->defaults as $key => $value)
            {
               $arr = array_add($arr,$key,$value);
            }
        }
        return $arr;
    }

    /**
     * Return controller name
     * 
     * @return string controller
     */
    public function getController() {
        return $this->controller;
    }

    /**
     * Return action
     * 
     * @return string action
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Return route name
     * 
     * @return string route name
     */
    public function getName() {
        return $this->name;
    }
	
    /**
     * Return route pattern
     * 
     * @return string route pattern
     */
    public function getPattern() {
        return $this->pattern;
    }
    
    /**
     * Return default parameters array
     * 
     * @return array default parameters array
     */
    public function getDefaults(){
        return $this->defaults;
    }

    public function getMethods(){
        return $this->methods;
    }
}