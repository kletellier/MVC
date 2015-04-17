<?php
 

namespace GL\Core\Debug;

use Symfony\Component\DependencyInjection\ContainerInterface;


Class TwigDebugBar extends \Twig_Extension
{
	protected $container;   

	public function __construct(ContainerInterface $container = null)
	{
		$this->container = $container;
		$this->debugbar = $this->container->get('debug');
		$this->renderer = $this->debugbar->getJavascriptRenderer();
		$url = BASE_PATH . "/dbg";
		$this->renderer->setBaseUrl($url);
	}

	public function getFunctions()
	{
		return array(
			'dbg_render' => new \Twig_Function_Method($this, 'render',  array('is_safe' => array('html'))),
			'dbg_renderHead'  => new \Twig_Function_Method($this, 'renderHead',  array('is_safe' => array('html')))
		);
	}

	public function render()
	{
		if(DEVELOPMENT_ENVIRONMENT)
		{
			return $this->renderer->render();
		}
		else
		{
			return "";
		}
	}

	public function renderHead()
	{
		if(DEVELOPMENT_ENVIRONMENT)
		{
			return $this->renderer->renderHead();
		}
		else
		{
			return "";
		}
		
	}

	public function getName()
	{
		return 'debugbar_extension';
	}
}
