<?php

/**
 * 
 * based on HttpKernel Symfony Exception
 * 
 */

namespace GL\Core\Exception;

class  MethodNotAllowedException extends HttpException
{
   
    public function __construct($message = null, \Exception $previous = null, $code = 0)
    {
        parent::__construct(405, $message, $previous, array(), $code);
    }
}
