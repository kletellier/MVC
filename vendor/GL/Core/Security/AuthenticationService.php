<?php 

namespace GL\Core\Security;

use GL\Core\Helpers\DbHelper as DB;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use GL\Core\Config\Config;
use GL\Core\Security\AuthenticationServiceInterface;

class AuthenticationService implements \GL\Core\Security\AuthenticationServiceInterface
{
	protected $request;
	protected $session;
	protected $config;
	protected $users_tablename;
	protected $roles_tablename;
	protected $usersroles_tablename; 
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
			$this->users_tablename = isset($this->config['security']['users_table']) ? $this->config['security']['users_table'] : 'users';
			$this->roles_tablename = isset($this->config['security']['roles_table']) ? $this->config['security']['roles_table'] : 'roles';
			$this->usersroles_tablename = isset($this->config['security']['usersroles_table']) ? $this->config['security']['usersroles_table'] : 'usersroles';
			
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
		$inst = $this->getInstance($this->roles_tablename);
		$arr = $inst->get();
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
	private function getInstance($tablename)
	{
		$name = "Application\\Models\\".ucfirst($tablename);
		if(class_exists($name))
		{
			return new $name();
		}
		else
		{
			return null;
		}
		
	}

	private function getUsersInstance()
	{
		return $this->getInstance($this->users_tablename);
	}

	private function getRolesInstance()
	{
		return $this->getInstance($this->roles_tablename);
	}

	private function getUsersRolesInstance()
	{
		return $this->getInstance($this->usersroles_tablename);
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
	public function userCreate($login,$email,$password)
	{
		$inst = $this->getUsersInstance();
		$ret = null;
		try 
		{
			$inst->login = $login;
			$inst->email = $email;
			$inst->salt = uniqid();
			$inst->password = $this->encryptPassword($inst->salt,$password);
			$inst->enabled = 0;
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
	 * Add role to user
	 * @param type $role_id 
	 * @return type
	 */
	public function addUserToRole($user_id,$role_id)
	{
		$roleinst = $this->getRolesInstance();
		$role = $roleinst->where('id','=',$role_id)->first();
		if($role!=null)
		{
			$urinst =  $this->getUsersRolesInstance();			 
			$links = $urinst->where('roles_id','=',$role_id)->where('users_id','=',$user_id)->get();
			if(count($links)==0)
			{
				$link = $this->getUsersRolesInstance();
				$link->roles_id = $role_id;
				$link->users_id = $user_id;
				$link->save();
			}
			 		
		}
		return $role;
	}

	/**
	 * Remove role to user
	 * @param type $role_id 
	 * @return type
	 */
	public function removeUserToRole($user_id,$role_id)
	{
		$urinst =  $this->getUsersRolesInstance();
		$links = $urinst->where('roles_id','=',$role_id)->where('users_id','=',$user_id)->get();
		foreach ($links as $link) 
		{
			$link->delete();
		}
	}

	/**
	 * Check if an user is created with specified login
	 * @param string $login login to test 
	 * @return boolean
	 */
	public function loginExist($login)
	{
		$ret = false;		 
		$inst = $this->getUsersInstance();
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
		$inst = $this->getUsersInstance();
		$user = $inst->where('email','=',$email)->first();
		if($user!=null)
		{
			$ret = true;			 
		}
		return $ret;
	}

	/**
	 * Create a role
	 * @param type $role 
	 * @param type $description 
	 * @return type
	 */
	public function roleCreate($role,$description)
	{
		$ret  = null;
		try 
		{
			$ret = $this->getRolesInstance();
			$ret->role = $role;
			$ret->description = $description;
			$ret->save();

		} catch (Exception $e) {
			$ret = null;
		}
		return $ret;
	}

	/**
	 * Delete role and link
	 * @param type $role_id 
	 * @return type
	 */
	public function roleDelete($role_id)
	{
		$ret = false;
		try 
		{
			$role = $this->getRolesInstance()->where('id','=',$role_id)->first();
			if($role!=null)
			{
				$role->delete();
			}
				$links = $this->getUsersRolesInstance()->where('role_id','=',$role_id)->get();
			foreach ($linsk as $link) 
			{
				$link->delete();
			}
			$ret = true;
		} catch (Exception $e) 
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
		$inst = $this->getUsersInstance();
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
	 * Load user from her login
	 * @param type $login 
	 * @return user instance
	 */
	public function userFromLogin($login)
	{
		$ret = null;

		$inst = $this->getUsersInstance();
		$ret = $inst->where('login','=',$login)->first();		 
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

		$inst = $this->getUsersInstance();
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

		$inst = $this->getUsersInstance();
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
		$inst = $this->getUsersInstance();
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

			if(isset($id) && $id=="")
			{
				$inst = $this->getUsersInstance();
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
			$inst = $this->getUsersInstance();
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
	public function userRoles($user_id="")
	{
		$id = $user_id;
		if($id=="")
		{
			// retrieve user logged
			$user = $this->userLogged();
			$id = $user->id;
		}
		$inst = $this->getUsersRolesInstance(); 
		$ids = $inst->where('users_id','=',$id)->get(array('roles_id'))->toArray(); 
		$roles = $this->getRolesInstance()->whereIn('id',$ids)->get();		 
		return $roles;
	}

	
	/**
	 * Give remenbertoken
	 * @return string remembertoken
	 */
	public function getRememberToken($id="")
	{
		$ret = "";
		try {
			$inst = $this->getUsersInstance();
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
			$inst = $this->getUsersInstance();
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
	private function createUsersTable()
	{
		$sch = DB::getSchema();
		$tablename = $this->users_tablename;
		if(!$sch->hasTable($tablename))
		{
			$sch->create($tablename, function($table)
			{
				$table->increments('id');
				$table->string('login')->unique();
				$table->string('password');
				$table->string('salt');
				$table->string('email')->unique();
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
	 * Create roles table
	 * @param string $tablename tablename in database
	 * @return void
	 */
	private function createRolesTable()
	{
		$sch = DB::getSchema();
		$tablename = $this->roles_tablename;
		if(!$sch->hasTable($tablename))
		{
			$sch->create($tablename, function($table)
			{
				$table->increments('id');
				$table->string('role')->unique();
				$table->string('description');				 			 
				$table->integer('enabled')->default(1);				 
				$table->timestamps();
			});
			// create model file
			$this->createModel($tablename,ucfirst($tablename));
			// create first role
			$inst = $this->getInstance($this->roles_tablename);
			$inst->role = "guest";
			$inst->description = "guest role";
			$inst->save();
		}	
	}

	/**
	 * Create roles table
	 * @param string $tablename tablename in database
	 * @return void
	 */
	private function createUsersRolesTable()
	{
		$sch = DB::getSchema();
		$tablename = $this->usersroles_tablename;
		if(!$sch->hasTable($tablename))
		{
			$sch->create($tablename, function($table)
			{
				$table->increments('id');
				$table->integer('roles_id');
				$table->integer('users_id');			 			 
				$table->timestamps();
			});
			// create model file
			$this->createModel($tablename,ucfirst($tablename));
		}	
	}


	public function createTables()
	{
		$this->createUsersTable();
		$this->createRolesTable();
		$this->createUsersRolesTable();
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

	/**
	 * Get role instance from is name
	 * @param type $name 
	 * @return type
	 */
	public function getRoleFromName($name)
	{
		$ret = false;
		$inst = $this->getRolesInstance();
		$ret = $inst->where('role','=',$name)->first();
		return $ret;
	}


}