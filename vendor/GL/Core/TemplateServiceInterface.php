<?php

namespace GL\Core;

interface TemplateServiceInterface
{
    public function render($template,array $params ,\Symfony\Component\DependencyInjection\Container $container = null,$controller = "",$disabledebug=false);
}