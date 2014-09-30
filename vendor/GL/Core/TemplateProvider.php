<?php 
namespace GL\Core;

use Symfony\Component\Yaml\Parser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOException;
 
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
            $path = TEMPLATECONF; 
            $fs = new Filesystem();
            $exist = $fs->exists(array($path));

            if($exist)
            {               
                $yaml = new Parser();
                $value = $yaml->parse(file_get_contents(TEMPLATECONF)); 
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

    public function getTemplateService()
    {
        $ret = null;
        try {
            if(isset($this->arr[TEMPLATE_ENGINE]))
            {
                $class =  $this->arr[TEMPLATE_ENGINE];
                $ret = new $class;
            }
            else
            {
                echo "Template engine ". TEMPLATE_ENGINE . "is not defined";
                die();                
            }
        } catch (Exception $e) {
            
        }
        return $ret;
    }

     
    
}