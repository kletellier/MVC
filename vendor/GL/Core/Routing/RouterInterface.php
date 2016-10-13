<?php

namespace GL\Core\Routing;

interface RouterInterface 
{
	public function __construct(\Symfony\Component\HttpFoundation\Request $request );
	public function setRequest(\Symfony\Component\HttpFoundation\Request $request);
    public function getRoute();
	public function getController();
	public function getMethod();
	public function getArgs();
	public function route($url);    
}
     