<?php

namespace GL\Core;

/**
 * Load Twig Environment
 *
 * @author kletellier
 */
class TwigService
{
     protected $_controller;
     
     function __construct($ctl)
     {
         $this->_controller = $ctl;
     }
     
     /**
     * Function for Twig path searching
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
     * Function returning the Twig environnment
     * 
     * @return \Twig_Environment
     */
    private function getTwigEnvironment()
    {   
        $arrcache = array();
        if(TWIG_CACHE)
        {
            $cachepath = CACHEPATH . DS . 'twig';            
            $arrcache = array('cache' => $cachepath,'auto_reload'=> AUTORELOADCACHE);
        }
        $twigloader = new \Twig_Loader_Filesystem($this->getPathArray());
        $twigenv = new \Twig_Environment($twigloader,$arrcache);
        $twigenv->addExtension(new \GL\Core\TwigHelper());
        $twigenv->addTokenParser(new \GL\Core\TwigRenderToken());
        return $twigenv;
    }
    
    /**
     * Render Twig template
     * 
     * @param string $template template path
     * @param array $params parameters array for template
     */
    public function render($template,array $params)
    {
        $env = $this->getTwigEnvironment();        
        return $env->render($template, $params);
    }
}
