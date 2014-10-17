<?php

namespace GL\Core;
 
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;

class Config
{

    protected $configname;


    /**
     * Constructor
     * @param string $configname name of config file without extension
     * @return void
     */
    public function __construct($configname  = "")
    {
        $this->configname = $configname;
    }

    public function setConfig($configname)
    {
        $this->configname = $configname;
    }

    /**
     * Get absolute path of the config file
     * @return string path
     */
    public function getPath()
    {
        return CONFIGDIR . DS . $this->configname . ".yml";
    }

    /**
     * Load data from yaml file
     * @param string $config config name to load
     * @return array data in yaml file
     */
    public function load($config="")
    {
        if($config!="")
        {
            $this->configname = $config;
        }
        $value = null;
        try 
        {
            $yaml = new Parser();
            $value = $yaml->parse(file_get_contents($this->getPath()));

        } 
        catch (Exception $e) 
        {
            
        }
        
        return $value;
    }

    /**
     * Save object to yaml file
     * @param object $object object to save in yaml file
     * @param string $config config file to save
     * @return void
     */
    public function save($object,$config="")
    {
        if($config!="")
        {
            $this->configname = $config;
        }
        $dumper = new Dumper();
        $yamltxt = $dumper->dump($object,2);
     
        if( file_put_contents($this->getPath(), $yamltxt)==FALSE)
        {
            throw new \Exception('Error saving config ' . $this->configname);
        }
    }
      

    
}
