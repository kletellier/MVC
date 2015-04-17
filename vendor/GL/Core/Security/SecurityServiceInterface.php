<?php

namespace GL\Core\Security;

interface SecurityServiceInterface
{
    public function __construct(\Symfony\Component\HttpFoundation\Session\Session $session,\Symfony\Component\HttpFoundation\Request $request);	  

	/**
	 * Return array of defined roles
	 * @return array all roles defined in security.yml
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
	 * @param array $roles 
	 * @return int
	 */
	public function userCreate($login,$email,$password,$roles);	

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
	 * return array of roles for user logged
	 * @return array
	 */
	public function userRoles();
	
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
	 * Create user table and model file
	 * @param string $tablename tablename in database
	 * @return void
	 */
	public function createTable();	 

}