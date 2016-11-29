<?php
 
namespace GL\Core\Security;
  

class PasswordHashing implements \GL\Core\Security\HashingInterface
{
	
	 public function hash($salt,$password)
	 {
	 	 return sha1($password.$salt);
	 }
	 
	 public function verify($salt,$password,$hash)
	 {
	 	$nhash = $this->hash($salt,$password);	 	 
	 	return ($nhash===$hash);
	 }
}