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
		$url = \GL\Core\Helpers\Utils::url("/dbg");
		$this->renderer->setBaseUrl($url);
	}

	public function getFunctions()
	{
		return array(
			new \Twig_SimpleFunction('dbg_render', array($this,'render'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('dbg_renderHead', array($this,'renderHead'), array('is_safe' => array('html'))),		 
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
