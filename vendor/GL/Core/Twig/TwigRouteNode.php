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
            ->raw("echo \GL\Core\Helpers\Utils::route(")
            ->subcompile($this->getNode('expr'))
            ->raw(",")
            ->subcompile($this->getNode('options'))
            ->raw(");\n") ;
        
    }
}