<?php
namespace GL\Core\Twig;

class TwigRenderNode extends \Twig_Node
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
            ->raw('$html="";')
            ->write('$cas = ')
            ->subcompile($this->getNode('expr'))
            ->raw(";\n")
            ->raw('$arr = explode("::",$cas);')
            ->raw('$controller = "";  $action = "";')             
            ->raw('$args = array();')            
            ->write('$args = ')
            ->subcompile($this->getNode('options'))
            ->raw(";\n")            
            ->raw('$arr = explode("::",$cas);')
            ->raw('  if(sizeof($arr)==2)')
            ->raw(' { ')
            ->raw(' $controller = $arr[0];')
            ->raw(' $action = $arr[1];         ')            
            ->raw(' $cr = new \GL\Core\Controller\ControllerResolver($controller,$action,$args);')
            ->raw(' $html = $cr->render();')
            ->raw(' } ')  
            ->raw('echo $html;')    
        ;        
         
    }
}