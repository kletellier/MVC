<?php 

namespace GL\Core\Security;

use GL\Core\Helpers\DbHelper as DB;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use GL\Core\Config\Config;


class SecurityService implements SecurityServiceInterface
{
	protected $request;
	protected $session;
	protected $config;
	protected $tablename;
	protected $modelname;
	protected $tokensalt;
	protected $cookiename;
	protected $cookieduration;
	protected $userlogged;

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
			$yaml = new Config('security');
			$value = $yaml->load();
			$this->config = $value;
			$this->tablename = isset($this->config['security']['table']) ? $this->config['security']['table'] : 'users';
			$this->tokensalt = $this->config['cookie']['token'];
			$this->cookiename = $this->config['cookie']['name'];
			$this->cookieduration = $this->config['cookie']['duration'];

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
		if(class_exists($name))
		{
			return new $name();
		}
		else
		{
			return null;
		}
		
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
	 * Return remember me cookie name
	 * @return string
	 */
	public function getRememberCookieName()
	{
		return $this->cookiename;
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
	 * Check if an user is created with specified login
	 * @param string $login login to test 
	 * @return boolean
	 */
	public function loginExist($login)
	{
		$ret = false;		 
		$inst = $this->getInstance();
		$user = $inst->where('login','=',$login)->first();
		if($user!=null)
		{
			$ret = true;			 
		}
		return $ret;
	}

	/**
	 * Check if an user is created with specified email
	 * @param string $email email to check
	 * @return boolean
	 */
	public function emailExist($email)
	{
		$ret = false;		 
		$inst = $this->getInstance();
		$user = $inst->where('email','=',$email)->first();
		if($user!=null)
		{
			$ret = true;			 
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
				$this->userlogged = $user;
			}
		}
		return $ret;
	}

	/**
	 * Load user from his email adresse
	 * @param string $mail email address
	 * @return User instance
	 */
	public function userFromMail($mail)
	{
		$ret = null;

		$inst = $this->getInstance();
		$ret = $inst->where('email','=',$mail)->first();		 
		return $ret;
	}

	/**
	 * Load user from his temporary key
	 * @param string $key temporary key stored in key column
	 * @return User instance
	 */
	public function userFromKey($key)
	{
		$ret = null;

		$inst = $this->getInstance();
		$ret = $inst->where('key','=',$key)->first();		 
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
			$this->userlogged = $user;	 
		}
		return $user;
	}

	/**
	 * Autologin script called from DI 
	 * @return void
	 */
	public function autologin()
	{
		
		$id = $this->session->get('session.id');
		try {
			if($id=="")
			{
				$inst = $this->getInstance();
				$token = $this->request->cookies->get($this->cookiename); 
				if($token!="")
				{
					$user = $inst->where('remember_token','=',$token)->first();
					if($user!=null)
					{		 	
						$this->session->set('session.id',$user->id);
						$this->session->save();
						$user->nblogin+=1;				 
						$user->save();		
						$this->userlogged = $user;	 
					}
				}			
			}
		} catch (Exception $e) {
			
		}

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
			if($id!="" && $this->userlogged==null)
			{
				$ret = $inst->find($id);
				$this->userlogged = $ret;				 
			}
			else
			{
				$ret = $this->userlogged;
			}
			
		} catch (Exception $e) {
			
		}
		return $ret;
	}

	/**
	 * Login from form request 
	 * @param string $logininput name of login input in submited form
	 * @param type $pwdinput name of password input in submited form
	 * @return boolean true if user is logged
	 */
	public function formLogin($logininput = "login",$pwdinput = "password")
	{
		$ret = false;
		try {
			$login = $this->request->get($logininput);
			$password = $this->request->get($pwdinput);
			$ret = $this->userLogin($login,$password);
			
		} catch (Exception $e) {
			$ret = false;
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
	public function getRememberToken($id="")
	{
		$ret = "";
		try {
			$inst = $this->getInstance();
			if($id=="")
			{
				$id = $this->session->get('session.id');
			}
			
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
	public function setRememberToken($id="")
	{
		$ret = "";
		try 
		{
			$inst = $this->getInstance();
			if($id=="")
			{
				$id = $this->session->get('session.id');
			}
			if($id!="")
			{
				$user = $inst->find($id);	
				if($user!=null)
				{
					$ret = sha1($user->salt . $this->tokensalt . uniqid());
					$user->remember_token = $ret;
					$user->save();
				}			 
			}
		} 
		catch (Exception $e) 
		{
			
		}
		return $ret;
	}

	/**
	 * Return Remember me Cookie
	 * @return Symfony\Component\HttpFoundation\Cookie
	 */
	public function getCookie()
	{
		$ret = null;
		$id = $this->session->get('session.id');

		if($id!="")
		{
			$token = $this->getRememberToken();
			if($token=="")
			{
				$token = $this->setRememberToken();	
			}
			$ret = new Cookie($this->cookiename, $token, time() + $this->cookieduration);
		}
		return $ret;
	}

	/**
	 * Disconnect login user
	 * @return void
	 */
	public function logout()
	{
		$this->userlogged = null;
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
				$table->integer('profile')->default(-1);
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