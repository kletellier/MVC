<?php
namespace GL\Core\Twig;

class TwigRouteNode extends \Twig_Node
{
    
    
     public function __construct(\Twig_Node_Expression $expr, \Twig_Node_Expression $options, $lineno, $tag = null)
    {
        parent::__construct(array('expr' => $expr, 'options' => $options), array(), $lineno, $tag);
         
    }

    /**
     * Compiles the node to PHP.
     *
     * @param \Twig_Compiler $compiler A Twig_Compiler instance
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler
            ->addDebugInfo($this)             
            ->write('$route = ')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")                     
            ->raw('$args = array();')            
            ->write('$args = ')
            ->subcompile($this->getNode('options'))
            ->raw(";\n")  
            ->raw(' $url = \GL\Core\Helpers\Utils::route($route,$args);')             
            ->raw('echo $url;')    
        ;        
         
    }
}