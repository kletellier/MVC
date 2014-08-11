<?php

namespace GL\Core;
 
use Symfony\Component\Finder\Finder;

/**
 * Load Php Template Environment
 *  
 */
class PhpTemplateService
{
     protected $_controller;
     protected $_container;
     
	 function __construct($container)
	 {
		 $this->_container = $container;
		 $this->_controller = $this->_container->getParameter('controller');           
	 }     
    
     /**
     * Function for Template path searching
     * TWIG_PATH , defined in index.php
     * TWIG_PATH / Controller name
     * 
     * @return string
     */
    private function getPathArray()
    {
        $viewctlpath = TWIGPATH . DS . ucfirst($this->_controller);
        $arr = array($viewctlpath,TWIGPATH);
        return $arr;
    }
    
    /**
     * Return path of selected template (first in controller subfolder and in view root folder)
     * @param string $template name of template todisplay
     * @return string Path of template finded
     */
    public function getPathTemplate($template)
    {		
        $str = "";	
        foreach($this->getPathArray() as $folder )
        {
            $finder = new Finder();
            $res = $finder->name($template)->in($folder)->files();
            foreach($res as $file)
            {
                $str = $file->getRealPath();
                break;
            }
        }
        return $str;
    }
}
