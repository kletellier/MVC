<?php 

namespace GL\Core\Events;

use Symfony\Component\EventDispatcher\Event;

class TestEvent extends Event
{
    const NAME = 'test.tested';

    protected $message;

    public function __construct($message)
    {
        $this->message = $message;
    }

    public function getMessage()
    {
        return $this->message;
    }
}