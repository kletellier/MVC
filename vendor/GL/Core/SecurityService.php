<?php 

namespace GL\Core;

use GL\Core\DbHelper as DB;
use Symfony\Component\Yaml\Parser;

class SecurityService
{
	protected $request;
	protected $config;
	protected $tablename;
	protected $modelname;
	protected $model = '<?php
 
namespace Application\Models;

use Illuminate\Database\Eloquent\Model; 

class ##modelname## extends Model {
	protected $table = "##tablename##";	 
    public $timestamps = false;
	 
}';

	public function __construct()
	{
		$this->loadConfig();
	}

	public function loadConfig()
	{
		try 
		{
			$yaml = new Parser();
			$value = $yaml->parse(file_get_contents(SECURITYPATH));
			$this->config = $value;
			$this->tablename = isset($this->config['security']['table']) ? $this->config['security']['table'] : 'users';
		} 
		catch (Exception $e) 
		{
			
		}
	}

	/**
	 * Return array of defined roles
	 * @return array all roles defined in security.yml
	 */
	public function getRoles()
	{
		$arr = array();
		if(isset($this->config['security']['roles']))
		{
			$arr = $this->config['security']['roles'];
		}
		return $arr;
	}

	/**
	 * Define Request 
	 * @param \Symfony\Component\HttpFoundation\Request $request 
	 * @return void
	 */
	public function setRequest(\Symfony\Component\HttpFoundation\Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Create user table
	 * @param string $tablename tablename in database
	 * @return void
	 */
	public function createTable()
	{
		$sch = DB::getSchema();
		$tablename = $this->tablename;
		if(!$sch->hasTable($tablename))
		{
			$sch->create($tablename, function($table)
			{
			    $table->increments('id');
			    $table->string('login');
			    $table->string('password');
			    $table->string('salt');
			    $table->string('email');
			    $table->text('roles');
			    $table->string('key');
			    $table->integer('enabled');
			    $table->rememberToken();
			    $table->integer('profile');
			    $table->integer('nblogin');
			    $table->timestamp('lastlogin');
			    $table->dateTime('created');
			});
			// create model file
			$this->createModel($tablename,ucfirst($tablename));
		}	
	}

	/**
	 * Create model file
	 * @param string $tablename  database table model
	 * @param string $mode model name
	 * @return void
	 */
	private function createModel($tablename ,$mode)
	{
		try {
			$nmodel = $this->model;
            $nmodel = str_replace('##modelname##',$mode,$nmodel);
            $nmodel = str_replace('##tablename##',$tablename,$nmodel);             		

            $path = ROOT . DS . "app" . DS . "Application" . DS . "models". DS;

            $filename = $path.$mode.".php";
            file_put_contents($filename,$nmodel);	

		} catch (Exception $e) {
			
		}
	}
 

}