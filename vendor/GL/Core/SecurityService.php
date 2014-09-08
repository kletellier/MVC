<?php 

namespace GL\Core;

use GL\Core\DbHelper as DB;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class SecurityService
{
	protected $request;
	protected $session;
	protected $config;
	protected $tablename;
	protected $modelname;
	protected $model = '<?php
 
namespace Application\Models;

use Illuminate\Database\Eloquent\Model; 

class ##modelname## extends Model {
	protected $table = "##tablename##";	 
    public $timestamps = true;
	 
}';

	public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session,\Symfony\Component\HttpFoundation\Request $request)
	{
		$this->setRequest($request);
		$this->setSession($session);
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
	private function setRequest(\Symfony\Component\HttpFoundation\Request $request)
	{
		$this->request = $request;
	}

	/**
	 * Define Session
	 * @param  \Symfony\Component\HttpFoundation\Session\Session $session 
	 * @return void
	 */
	private function setSession(\Symfony\Component\HttpFoundation\Session\Session $session)
	{
		$this->session = $session;
	}

	/**
	 * Return model instance 
	 * @return Model instance
	 */
	private function getInstance()
	{
		$name = "Application\\Models\\".ucfirst($this->tablename);
		return new $name();
	}


	/**
	 * Password encryption
	 * @param string $salt 
	 * @param string $password 
	 * @return string password encrypted
	 */
	public function encryptPassword($salt,$password)
	{
		$tmp = $password.$salt;
		return sha1($tmp);
	}

	/**
	 * Test if user password is ok
	 * @param UserObject $user instance of user model 
	 * @param string $password password to check
	 * @return boolean
	 */
	public function testPassword($user,$password)
	{
		$pass = $user->password;
		$passe = $this->encryptPassword($user->salt,$password);
		return ($pass==$passe);
	}

	/**
	 * Create user
	 * @param string $login 
	 * @param string $email 
	 * @param string $password 
	 * @param array $roles 
	 * @return int
	 */
	public function userCreate($login,$email,$password,$roles)
	{
		$inst = $this->getInstance();
		$ret = null;
		try 
		{
			$inst->login = $login;
			$inst->email = $email;
			$inst->salt = uniqid();
			$inst->password = $this->encryptPassword($inst->salt,$password);
			$inst->roles = json_encode($roles);
			$inst->enabled = 1;
			$inst->key = uniqid();			
			$inst->save();
			$ret = $inst->id;
		} 
		catch (Exception $e) 
		{
			
		}
		return $ret;
	}

	/**
	 * Return true if user is logged
	 * @param string $login user login
	 * @param string $password user password
	 * @return boolean
	 */
	public function userLogin($login,$password)
	{
		$ret = false;
		$this->logout();
		$inst = $this->getInstance();
		$user = $inst->where('login','=',$login)->first();
		if($user!=null)
		{
			$ret = $this->testPassword($user,$password);
			if($ret)
			{				

				$this->session->set('session.id',$user->id);
				$this->session->save();
				$user->nblogin+=1;				 
				$user->save();
			}
		}
		return $ret;
	}

	/**
	 * Login by remenberme token
	 * @param string $token rememberme token stored in cookie
	 * @return boolean
	 */
	public function userAutoLogin($token)
	{
		$ret = false;
		$this->logout();
		$inst = $this->getInstance();
		$user = $inst->where('remember_token','=',$token)->first();
		if($user!=null)
		{		 	
			$this->session->set('session.id',$user->id);
			$this->session->save();
			$user->nblogin+=1;				 
			$user->save();			 
		}
		return $ret;
	}

	/**
	 * Return user actually logged
	 * @return User model instance
	 */
	public function userLogged()
	{
		$ret = null;
		try {
			$inst = $this->getInstance();
			$id = $this->session->get('session.id');
			if($id!="")
			{
				$ret = $inst->find($id);				 
			}
			
		} catch (Exception $e) {
			
		}
		return $ret;
	}

	/**
	 * return array of roles for user logged
	 * @return array
	 */
	public function userRoles()
	{
		$ret = array();
		$user = $this->userLogged();
		if($user!=null)
		{
			$ret = json_decode($user->roles);
		}
		return $ret;
	}

	/**
	 * Give remenbertoken
	 * @return string remembertoken
	 */
	public function getRememberToken()
	{
		$ret = "";
		try {
			$inst = $this->getInstance();
			$id = $this->session->get('session.id');
			if($id!="")
			{
				$user = $inst->find($id);	
				if($user!=null)
				{
					$ret = $user->remember_token;
				}			 
			}
			
		} catch (Exception $e) {
			
		}
		return $ret;
	}

	/**
	 * Set cookie remenber token
	 * @return string
	 */
	public function setRememberToken()
	{
		$ret = "";
		try 
		{
			$inst = $this->getInstance();
			$id = $this->session->get('session.id');
			if($id!="")
			{
				$user = $inst->find($id);	
				if($user!=null)
				{
					$token = sha1($user->salt . uniqid());
					$user->remember_token = $token;
					$user->save();
				}			 
			}
		} 
		catch (Exception $e) 
		{
			
		}
	}

	/**
	 * Disconnect login user
	 * @return void
	 */
	public function logout()
	{
		$this->session->set('session.id',"");
		$this->session->save();
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
			    $table->string('login')->unique();
			    $table->string('password');
			    $table->string('salt');
			    $table->string('email')->unique();
			    $table->text('roles');
			    $table->string('key');
			    $table->integer('enabled')->default(0);
			    $table->rememberToken();
			    $table->integer('profile');
			    $table->integer('nblogin')->default(0);
			    $table->timestamps();
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

            $path = ROOT . DS . "app" . DS . "Application" . DS . "Models". DS;

            $filename = $path.$mode.".php";
            file_put_contents($filename,$nmodel);	

		} catch (Exception $e) {
			
		}
	}
 

}