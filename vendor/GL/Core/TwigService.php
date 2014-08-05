<?php

namespace GL\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Load Twig Environment
 *
 * @author kletellier
 */
class TwigService
{
     protected $_controller;
     protected $_container;
     
     function __construct($ctl)
     {
         $this->_controller = $ctl;   
         $this->_container = null;
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
        // Cache only allowed in prod mode
        if(TWIG_CACHE && !DEBUGMODE_ENABLED)
        {
            $cachepath = CACHEPATH . DS . 'twig';            
            $arrcache = array('cache' => $cachepath,'auto_reload'=> AUTORELOADCACHE);
        }
        if(DEBUGMODE_ENABLED)
        {
            $arrcache = array('debug' => true);
        }
        $twigloader = new \Twig_Loader_Filesystem($this->getPathArray());
        $twigenv = new \Twig_Environment($twigloader,$arrcache);        
        $twigenv->addExtension(new \GL\Core\TwigHelper($this->_container));
        if(DEBUGMODE_ENABLED)
        {
          $twigenv->addExtension(new \Twig_Extension_Debug());  
        }
        $twigenv->addTokenParser(new \GL\Core\TwigRenderToken());
        return $twigenv;
    }
    
    /**
     * Embed DI container in TwigHelper
     * 
     * @param \GL\Core\ContainerInterface $container DI contrainer to embed in TwigHelper
     */
     private function setContainer(\Symfony\Component\DependencyInjection\ContainerBuilder $container = null)
     {
         $this->_container = $container;
     }
    
    /**
     * Render Twig template
     * 
     * @param string $template template path
     * @param array $params parameters array for template
     */
    public function render($template,array $params,\Symfony\Component\DependencyInjection\ContainerBuilder $container = null)
    {
        $this->setContainer($container);
        $env = $this->getTwigEnvironment();        
        return $env->render($template, $params);
    }
}
