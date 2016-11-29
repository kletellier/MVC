<?php
 
namespace GL\Core\Security;
  
class BcryptHashing  implements \GL\Core\Security\HashingInterface
{
	public function hash($salt,$password)
	{
		return password_hash($password.$salt,PASSWORD_BCRYPT);
	}
	
	public function verify($salt,$password,$hash)
	{	 	 	 
		return password_verify($password.$salt,$hash);
	} 
}