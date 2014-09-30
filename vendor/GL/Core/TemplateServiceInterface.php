<?php

namespace GL\Core;

interface TemplateServiceInterface
{
    public function render($template,array $params = array(),\Symfony\Component\DependencyInjection\Container $container = null,$controller = "");
}