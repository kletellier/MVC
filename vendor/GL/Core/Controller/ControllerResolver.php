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
  function __construct($controller, $action,$args) 
        {
            $this->_controller = ucfirst($controller);
            $this->_action = $action;
            $this->_args = $args;
            $this->_errors = array();
            // return DI Container
            $this->_container = ServiceProvider::GetDependencyContainer();             
      }
        
        /**
         * 
         * Return Error 500 Response
         * 
         * @param String $message Message to be displayed on error page
         * @param String $file File where the error is located
         * @param type $line Line error in file
         */
        private function get500Response($message,$file,$line)
        {             
            ob_clean();            
            $params = array('message'=>$message,'file'=>$file,'line'=>$line,'errors'=>$this->_errors);  
            $this->_controller = "error";
            $this->_action = "error500";
            $this->_args = $params;           
            return  $this->execute();                
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
      * Fatal error handler
      */
       function ShutdownError()
        {
            $error = error_get_last();
            if (!empty($error)) 
            {
                // on a une erreur fatale
                $message = $error['message'];
                $fichier = $error['file'];
                $line = $error['line'];
                                
                if(!DEVELOPMENT_ENVIRONMENT)
                {
                    $message = $this->trans('error.oops',"Oops fatal error happens...");
                    $fichier = "";
                    $line = "";                    
                }
                
                $response = $this->get500Response($message, $fichier, $line); 
                $response->send();                  
                exit(0);
            }
            else
            {
                if(!empty($this->_errors) && DEVELOPMENT_ENVIRONMENT)
                {
                    $message = $this->trans('error.snf',"Some non fatal error are detecting....");
                    $response =$this->get500Response($message, "", "");  
                    $response->send();  
                    exit(0);
                }
            }
        }
        
       /**
        * 
        * Non blocking error handler
        *         
        * 
        * @param String $errno Error number
        * @param String $errstr Error message
        * @param String $errfile Error file location
        * @param String $errline Error line number
        * @return boolean
        */
        function ErrorHandler($errno, $errstr, $errfile, $errline)
        {            
            switch ($errno) 
            {
                case E_NOTICE:                   
                case E_USER_NOTICE:
                    $errors = "<b>" . $this->trans('error.notice','Notice') . "</b>";
                    break;
                case E_WARNING:
                case E_USER_WARNING:
                    $errors = "<b>" . $this->trans('error.warning','Warning') . "</b>";
                    break;
                case E_ERROR:
                case E_USER_ERROR:
                    $errors = "<b>" . $this->trans('error.fatal','Fatal error') . "</b>";
                    break;
                default:
                    $errors = "<b>" . $this->trans('error.error','Error') . "</b>";
                    break;
            }
            
            $message = $errors." : ".$errstr;            
            $arr = array('message'=>$message,'line'=>$errline,'file'=>$errfile);                
            array_push($this->_errors,$arr);            
            return true;
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

                if(DEVELOPMENT_ENVIRONMENT)
                {
                   $this->_container->get('debug')["messages"]->addMessage("Controller : " . $controllerName);
                   $this->_container->get('debug')["messages"]->addMessage("Action : " . $this->_action);  
                }  

                if ((int)method_exists($controllerName, $this->_action)) 
                { 

                    // non fatal error handling
                    set_error_handler(array(&$this, 'ErrorHandler'));   
                    // fatal error handling
                    register_shutdown_function(array(&$this,'ShutdownError'));
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
                $response = $this->get403Response();
            }
            catch(\GL\Core\Exception\NotFoundHttpException $nf)
            {
                $response = $this->get404Response();
            }
            catch(Exception $ex)
            {                   
               $response = $this->get500Response($ex->getMessage()  , "", "");  
            }
            return $response;
     }        
  
     function __destruct() 
        {
    
       }
}