<?php

namespace GL\Core\Templating;

interface TemplateServiceInterface
{
	public function setController($controller = "");

    public function setContainer(\Symfony\Component\DependencyInjection\Container $container = null);

    public function render($template,array $params , $disabledebug=false);
}