<?php 

namespace GL\Core\Security;

use Symfony\Component\EventDispatcher\Event;

class SecurityEvent extends Event
{
    const SECURITY_STARTED = 'security.started';
    const SECURITY_USER_LOGGED = 'security.userlogged';

    protected $user;
    
    public function __construct($userInstance)
    {
        $this->user = $userInstance;         
    }

    public function getUserLogged()
    {
        return $this->user;
    }
    
}