<?php

namespace GL\Core\Security;

interface AuthenticationServiceInterface
{
    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session,\Symfony\Component\HttpFoundation\Request $request);	  

	/**
	 * Return array of defined roles
	 * @return array all roles defined 
	 */
	public function getRoles();

	/**
	 * Password encryption
	 * @param string $salt 
	 * @param string $password 
	 * @return string password encrypted
	 */
	public function encryptPassword($salt,$password);

	/**
	 * Test if user password is ok
	 * @param UserObject $user instance of user model 
	 * @param string $password password to check
	 * @return boolean
	 */
	public function testPassword($user,$password);	

	/**
	 * Return remember me cookie name
	 * @return string
	 */
	public function getRememberCookieName();	

	/**
	 * Create user
	 * @param string $login 
	 * @param string $email 
	 * @param string $password 
	 * @return int
	 */
	public function userCreate($login,$email,$password);	

	/**
	 * Create a role
	 * @param type $role 
	 * @param type $description 
	 * @return Role model
	 */
	public function roleCreate($role,$description);

	/**
	 * Delete a role
	 * @param type $role_id 
	 * @return type
	 */
	public function roleDelete($role_id);
	
	/**
	 * Check if an user is created with specified login
	 * @param string $login login to test 
	 * @return boolean
	 */
	public function loginExist($login);	

	/**
	 * Check if an user is created with specified email
	 * @param string $email email to check
	 * @return boolean
	 */
	public function emailExist($email);	

	/**
	 * Return true if user is logged
	 * @param string $login user login
	 * @param string $password user password
	 * @return boolean
	 */
	public function userLogin($login,$password);	


	/**
	 * Load user from her login
	 * @param type $login 
	 * @return user instance
	 */
	public function userFromLogin($login);

	/**
	 * Load user from his email adresse
	 * @param string $mail email address
	 * @return User instance
	 */
	public function userFromMail($mail);	

	/**
	 * Load user from his temporary key
	 * @param string $key temporary key stored in key column
	 * @return User instance
	 */
	public function userFromKey($key);	

	/**
	 * Login by remenberme token
	 * @param string $token rememberme token stored in cookie
	 * @return boolean
	 */
	public function userAutoLogin($token);	

	/**
	 * Autologin script called from DI 
	 * @return void
	 */
	public function autologin();	

	/**
	 * Return user actually logged
	 * @return User model instance
	 */
	public function userLogged();	

	/**
	 * Login from form request 
	 * @param string $logininput name of login input in submited form
	 * @param type $pwdinput name of password input in submited form
	 * @return boolean true if user is logged
	 */
	public function formLogin($logininput = "login",$pwdinput = "password");
	
	/**
	 * return array of roles for user
	 * @return array
	 */
	public function userRoles($user_id="");

	/**
	 * Return if user is member in one of 
	 * @param Array $roles array of role name
	 * @return type
	 */
	public function userIsRoleMember(Array $roles);

	/**
	 * Return array of users for specific role
	 * @param int $role_id  role id
	 * @return array of users instance
	 */
	public function roleUsers($role_id);
	
	/**
	 * Give remenbertoken
	 * @return string remembertoken
	 */
	public function getRememberToken($id="");	

	/**
	 * Set cookie remenber token
	 * @return string
	 */
	public function setRememberToken($id="");	

	/**
	 * Return Remember me Cookie
	 * @return Symfony\Component\HttpFoundation\Cookie
	 */
	public function getCookie();	

	/**
	 * Disconnect login user
	 * @return void
	 */
	public function logout();	

	/**
	 * Create users,roles,roles_users tables and models files
	 * @param string $tablename tablename in database
	 * @return void
	 */
	public function createTables();	 

	/**
	 * Add role to one user
	 * @param type $role_id 
	 * @return type
	 */
	public function addUserToRole($user_id,$role_id);

	/**
	 * Remove role to one user
	 * @param type $role_id 
	 * @return type
	 */
	public function removeUserToRole($user_id,$role_id);

	/**
	 * Get role instance from is name
	 * @param array array of name 
	 * @return array of roles instance
	 */
	public function getRolesFromName($name);
}