<?php

namespace GL\Core\Templating;

interface TemplateServiceInterface
{
    public function render($template,array $params ,\Symfony\Component\DependencyInjection\Container $container = null,$controller = "",$disabledebug=false);
}