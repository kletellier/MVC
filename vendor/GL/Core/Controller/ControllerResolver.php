<?php

namespace GL\Core\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Reference;
use GL\Core\DI\ServiceProvider;

/**
 * 
 * Controller Resolver
 * Class to execute a controller action from specified route
 * 
 */
class ControllerResolver
{
    protected $_controller;
    protected $_action;  
    protected $_args;
    protected $_errors;
    protected $_container;
    
    /**
     * Constructor
     * 
     * @param String $controller Controller 
     * @param String $action Function to execute in controller
     * @param Array $args Arguments extracted from route
     * 
     */
  function __construct($controller, $action,$args,$container=null) 
        {
            $this->_controller = ucfirst($controller);
            $this->_action = $action;
            $this->_args = $args;
            $this->_errors = array();             
            $this->_container = $container;             
      }      

      public function setContainer($container)
      {
        $this->_container = $container;
      }

        public function addDebug($str)
        {
            if(DEVELOPMENT_ENVIRONMENT){
                $this->_container->get('debug')["messages"]->addMessage($str);
            }
           
        }  
               
         /**
         * 
         * Return Error 404 Response
         *         
         */
        private function get404Response()
        {
            ob_clean();
            $this->_controller = "error";
            $this->_action = "error404";
            $this->_args = array();             
            return $this->execute();           
        }
        
        /**
         * Return Errror 430 response
         */
        private function get403Response()
        {
            ob_clean();
            $this->_controller = "error";
            $this->_action = "error403";
            $this->_args = array();             
            return $this->execute(); 
        }
        
          /**
         * Return Errror 401 response
         */
        private function get401Response()
        {
            ob_clean();
            $this->_controller = "error";
            $this->_action = "error401";
            $this->_args = array();             
            return $this->execute(); 
        }
        

        /**
         * Translate error message
         * @param string $code message code to translate
         * @param string $default default value to display
         * @return string string translated in locale
         */
        function trans($code,$default)
        {
          $ret = $default;
          if($this->_container!=null)
          {
            $ts = $this->_container->get('translator');
            $tmp = $ts->translate($code);
            if($tmp!="")
            {
              $ret=$tmp;
            }
          }
          return $ret;
        }  
        
      /**
       * 
       * Function return Controller namespace
       * 
       * @return String Controller namespace
       */
        private function getControllerName()
        {
            return 'Application\\Controllers\\'.ucfirst($this->_controller).'Controller';
        }
        
     /**
      * 
      * Return a controller instance
      * 
      * @param String $controllername Controller namespace to instanciate
      * @return \GL\Core\Controller\Controller Herited instance of controller
      */
        private function getInstance($controllername)
        {           
            $instance =  new $controllername($this->_controller,$this->_action);
            // add dependency container in the controller instance
            $instance->setContainer($this->_container);
            return $instance;
        }
        
       /**
        * Function to extract required parameters for action with route paramaters extracted
        * 
        * @param \GL\Core\Controller\Controller $instance Controller instance
        * @return Array Arguments array for execute action function
        */
        private function getArguments($instance)
        {
            $r = new \ReflectionMethod($instance, $this->_action);                        
            $arguments = $this->RetrieveArguments($this->_args,$r->getParameters());
            return $arguments;
        }
               
       /**
        * Extract arguments from route parsing parameters
        * 
        * @param array $attributes required parameters for action function
        * @param array $parameters extracted from route parsing
        * @return array
        */
        private function RetrieveArguments(array $attributes, array $parameters)
        { 
            $arguments = array();
            foreach ($parameters as $param) 
            {
                if (array_key_exists($param->name, $attributes))
                {
                    $arguments[] = $attributes[$param->name];
                }  
            }
            return $arguments;
        }                   
        
      /**
       * Execute action in controller and render only text (no response)
       * 
       * For Twig partial rendering : {% render 'controller::action',{params} %}
       * 
       * @return string
       */
        function render()
        {
            $ret = "";
            try 
            {       
                $controllerName = $this->getControllerName();
                $dispatch = $this->getInstance($controllerName);
                if ((int)method_exists($controllerName, $this->_action)) 
                {               
                    $ret = call_user_func_array(array($dispatch,$this->_action),$this->getArguments($dispatch));        
                } else 
                {
                    $ret = "";
                } 
            }
            catch(Exception $ex)
            {
                    $ret = "";
            }
            return $ret;
        }              
  
      /**
       * Execute action in selected controller (normally return a Response object)
       */
      function execute()
      {        
            $response = null;
            try 
            {       
                $controllerName = $this->getControllerName();
                $dispatch = $this->getInstance($controllerName);  
            
                $this->addDebug("Controller : " . $controllerName);
                $this->addDebug("Action : " . $this->_action);                   

                if ((int)method_exists($controllerName, $this->_action)) 
                { 
                    $params = $this->getArguments($dispatch); 
                    $response = call_user_func_array(array($dispatch,$this->_action),$params);                   
                }
                else 
                {
                    $response = $this->get404Response();                   
                } 

            }
            catch(\GL\Core\Exception\AccessDeniedHttpException $ad)
            {
                $response = $this->get401Response();
            }
            catch(\GL\Core\Exception\AccessForbiddenHttpException $ad)
            {
                $response = $this->get403Response();
            }
            catch(\GL\Core\Exception\NotFoundHttpException $nf)
            {
                $response = $this->get404Response();
            }          
            return $response;
     }        
  
     function __destruct() 
        {
    
       }
}