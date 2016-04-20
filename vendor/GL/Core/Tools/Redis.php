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
    
    public function __construct()
    {    
        $this->init();
    }

    private function init()
    {         
        $values = \Parameters::get('redis');
        $enable = isset($values['default']['enable']) ? $values['default']['enable'] : 0;
        $this->client = null;
        if($enable==1)
        {
            $this->client = new \Predis\Client([
            'scheme' => 'tcp',
            'host'   => $values['default']['server'],
            'port'   => $values['default']['port'],
            ]);
        }        
    }    

    public function getRedisClient()
    {
        return $this->client;
    }

    public function command($method, array $parameters = [])
    {
        return call_user_func_array([$this->client, $method], $parameters);
    }

    public function __call($method, $parameters)
    {
        return $this->command($method, $parameters);
    }

}
