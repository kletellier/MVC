<?php 
namespace GL\Core\Templating;

use GL\Core\Config\Config; 
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
use Assert\Assertion;

class TemplateProvider  
{   

    protected $arr;
    protected $instance;

    public function __construct()
    {        
         
        $this->arr = array();
        $this->instance = null;
        $this->loadFile();
    }     

    public function loadFile()
    {
        $ret = false;
        try 
        {
            $config = new Config('templates');
            $path = $config->getPath(); 
            $fs = new Filesystem();
            $exist = $fs->exists(array($path));

            if($exist)
            {  
                $value = $config->load(); 
                foreach($value as $name => $th)
                {
                    $this->arr[$name] = $th['class'];                    
                }
            }           
        } 
        catch (IOException $e) {
            $ret = false;
        }
        catch (Exception $e) {
            $ret = false;
        }   
        return $ret;
    }

    public function getTemplateService($eng="")
    {
        $ret = null;
        // spécify engine or use default         
        $engine = ($eng == "") ? TEMPLATE_ENGINE : $eng;  
        try 
        {
            Assertion::keyIsset($this->arr,$engine,"Template engine $engine  is not defined");           
            $class =  $this->arr[$engine];
            Assertion::ClassExists($class);
            $ret = new $class;          
        } 
        catch (Exception $e) {
            
        }

        return $ret;
    }
     
    
}