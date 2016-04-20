<?php

namespace GL\Core\Config;
 
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class Functions
{

    protected $configname;
    protected $parameters;


    /**
     * Constructor
     * @return void
     */
    public function __construct()
    {
        $this->load();
    }
 

    /**
     * Get absolute path of the config file
     * @return string path
     */
    private function getPath()
    {
        return CONFIGDIR . DS . "functions.yml";
    }

    /**
     * Return section of parameters file
     * @param string $key key value
     * @return tyarraype
     */
    public function get($key)
    {
        $ret = null;
        try 
        {
           if(isset($this->parameters[$key]))
           {
            $ret = $this->parameters[$key];
           } 
        } 
        catch (Exception $e) 
        {
            
        }
        return $ret;
    }

    /**
     * Return all parameters
     * @return arrray
     */
    public function getAll()
    {
        return $this->parameters;
    }

    /**
     * Load  parameters data from yaml file
     */
    private function load()
    {
        
        $this->parameters = null;
        try 
        {
            $yaml = new Parser();
            $this->parameters = $yaml->parse(file_get_contents($this->getPath()));

        } 
        catch (Exception $e) 
        {
            
        }       
        
    }
 
      

    
}
