<?php

namespace GL\Core\Twig;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Load Twig Environment
 *
 */
class TwigService implements \GL\Core\Templating\TemplateServiceInterface
{
     protected $_controller;
     protected $_container;
     
     function __construct($controller = "")
     {
         $this->_controller = $controller;   
         $this->_container = null;
     }
     
     /**
      * set Controller name
      * @return void
      */
     private function setController($controller = "")
     {        
        $this->_controller = $controller;
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
        

        if(isset($this->_controller))
        {
            $viewctlpath = TEMPLATEPATH . DS . ucfirst($this->_controller);
            $arr = array($viewctlpath,TEMPLATEPATH);
        }
        else
        {           
            $arr = array(TEMPLATEPATH);
        }        

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
       
        if(TEMPLATE_CACHE )
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
        $twigenv->addExtension(new \GL\Core\Twig\TwigHelper($this->_container));
        
        // add shared TwigHelper
        $config = new \GL\Core\Config\Config('twig');
        $value = $config->load(); 
        foreach($value as $name => $th)
        {
            $class = $th['class'];
            $twigenv->addExtension(new $class($this->_container));
        }
        //$twigenv->addExtension(new \Application\Shared\SharedTwigHelper($this->_container));
        if(DEVELOPMENT_ENVIRONMENT)
        {
          $twigenv->addExtension(new \GL\Core\Debug\TwigDebugBar($this->_container));
          $twigenv->addExtension(new \Twig_Extension_Debug());  
        }
        $twigenv->addTokenParser(new \GL\Core\Twig\TwigRenderToken());
        $twigenv->addTokenParser(new \GL\Core\Twig\TwigRouteToken());

        return $twigenv;
    }
    
    /**
     * Embed DI container in TwigHelper
     * 
     * @param \GL\Core\ContainerInterface $container DI contrainer to embed in TwigHelper
     */
     private function setContainer(\Symfony\Component\DependencyInjection\Container $container = null)
     {
         $this->_container = $container;
     }
    
    /**
     * Render Twig template
     * 
     * @param string $template template path
     * @param array $params parameters array for template
     */ 
    public function render($template,array $params,\Symfony\Component\DependencyInjection\Container $container = null,$controller="",$disabledebug=false)
    {   
        $ret = "";
        try 
        {
            $stopwatch = new Stopwatch();
            $stopwatch->start('render');
            $this->setContainer($container);  
            $this->setController($controller);
            $env = $this->getTwigEnvironment();
            if(DEVELOPMENT_ENVIRONMENT && $disabledebug==false)
            {
                $envdebug = new \DebugBar\Bridge\Twig\TraceableTwigEnvironment($env);
                $container->get('debug')->addCollector(new \DebugBar\Bridge\Twig\TwigCollector($envdebug));             
                $ret =  $envdebug->render($template, $params);
            }
            else
            {
                $ret =  $env->render($template, $params);
            }
            
            $event = $stopwatch->stop('render');
            /*if(DEVELOPMENT_ENVIRONMENT)
            {
                echo "<!-- generated  twig  ".$event->getDuration()." ms -->\r\n";
            } */       
        } 
        catch (\Twig_Error $e) 
        {
            if($container!=null && DEVELOPMENT_ENVIRONMENT)
            {

                $container->get('debug')["exceptions"]->addException($e);
            }
           throw new \Exception($e->getMessage());
        }
        
        return $ret;
    }
}
