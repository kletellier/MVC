<?php

namespace GL\Core;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Parser;

/**
 * Load Twig Environment
 *
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
        
        if(TWIG_CACHE )
        {
            $cachepath = CACHEPATH . DS . 'twig';            
            $arrcache['cache'] =  $cachepath;
            $arrcache['auto_reload'] = AUTORELOADCACHE;
        }
        if(DEVELOPMENT_ENVIRONMENT)
        {
            $arrcache['debug']=true;
        }
        $twigloader = new \Twig_Loader_Filesystem($this->getPathArray());
        $twigenv = new \Twig_Environment($twigloader,$arrcache);        
        $twigenv->addExtension(new \GL\Core\TwigHelper($this->_container));
        // add shared TwigHelper
        $yaml = new Parser();
        $value = $yaml->parse(file_get_contents(TWIGHELPER)); 
        foreach($value as $name => $th)
        {
            $class = $th['class'];
            $twigenv->addExtension(new $class($this->_container));
        }
        //$twigenv->addExtension(new \Application\Shared\SharedTwigHelper($this->_container));
        if(DEVELOPMENT_ENVIRONMENT)
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
