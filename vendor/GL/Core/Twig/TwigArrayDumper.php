<?php

namespace GL\Core\Twig;

 
class TwigArrayDumper
{ 
    private $_array;

    public function dump(\Twig_Profiler_Profile $profile)
    {        
        $this->_array = array();
        $this->dumpProfile($profile);
        return $this->_array;
    }

    private function format(\Twig_Profiler_Profile $profile)
    {
    	$tmp = array();
    	$tmp["template"] = $profile->getTemplate();
    	$tmp["name"] = $profile->getTemplate();
        $tmp["duration"] = $profile->getDuration();
    	$tmp["type"] = $profile->getType();
        return $tmp;
    }    

    private function dumpProfile(\Twig_Profiler_Profile $profile)
    {
    	$tmp = $this->format($profile);
        if($tmp["type"]=="template")
        {
            $this->_array[] = $tmp;
        }    	
    	foreach ($profile->getProfiles() as $prf) 
    	{
    		$this->dumpProfile($prf);
    	}        
    }
}
