<?php
 
namespace GL\Core\Security;

interface HashingInterface
{
	
	 public function hash($salt,$password);
	 
	 public function verify($salt,$password,$hash);
}