<?php

namespace GL\Core\Tools;
 
use GL\Core\Config\Config;
use Predis\Client;

/**
 * Class Redis
 */
class Redis 
{   
    protected $client;
    protected $commands;
    
    public function __construct()
    {    
        $this->init();
    }

    private function init()
    {         
        $this->commands = array();
        $values = \Parameters::get('redis');

        $enable = isset($values['default']['enable']) ? $values['default']['enable'] : 0;
        $pwd = isset($values['default']['password']) ? $values['default']['password'] : "";
        $this->client = null;
        if($enable==1)
        {
            $params = [
            'scheme' => 'tcp',
            'host'   => $values['default']['server'],
            'port'   => $values['default']['port'],
            ];
            if(trim($pwd)!="")
            {
                $params['password'] = $pwd;
            }   
            $this->client = new \Predis\Client($params);
        }  
    }    

    public function getCommandsHistory()
    {
        return $this->commands;
    }

    public function getRedisClient()
    {
        return $this->client;
    }

    public function command($method, array $parameters = [])
    {
        if(DEVELOPMENT_ENVIRONMENT)
        {   
            $this->commands[] = array('command'=>$method,'parameters'=>$parameters);
        }        
        return call_user_func_array([$this->client, $method], $parameters);
    }

    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }

}
