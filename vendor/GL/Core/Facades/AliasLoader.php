<?php 
/**
 * Sourcecode modified from Laravel Framework
 */
namespace GL\Core\Facades;

class AliasLoader {

	 
	protected $aliases;

 
	protected $registered = false;

 
	protected static $instance;

	 
	public function __construct(array $aliases = array())
	{
		$this->aliases = $aliases;
	}

 
	public static function getInstance(array $aliases = array())
	{
		if (is_null(static::$instance)) static::$instance = new static($aliases);
		
		$aliases = array_merge(static::$instance->getAliases(), $aliases);

		static::$instance->setAliases($aliases);

		return static::$instance;
	}

	 
	public function load($alias)
	{
		if (isset($this->aliases[$alias]))
		{
			return class_alias($this->aliases[$alias], $alias);
		}
	}

	 
	public function alias($class, $alias)
	{
		$this->aliases[$class] = $alias;
	}

	 
	public function register()
	{
		if ( ! $this->registered)
		{
			$this->prependToLoaderStack();

			$this->registered = true;
		}
	}

	 
	protected function prependToLoaderStack()
	{
		spl_autoload_register(array($this, 'load'), true, true);
	}

 
	public function getAliases()
	{
		return $this->aliases;
	}

	 
	public function setAliases(array $aliases)
	{
		$this->aliases = $aliases;
	}

	 
	public function isRegistered()
	{
		return $this->registered;
	}

	 
	public function setRegistered($value)
	{
		$this->registered = $value;
	}

 
	public static function setInstance($loader)
	{
		static::$instance = $loader;
	}

}
