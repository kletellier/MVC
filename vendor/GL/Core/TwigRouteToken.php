<?php 
namespace GL\Core;

class TwigRouteToken extends \Twig_TokenParser
{ 
    
     
    public function parse(\Twig_Token $token)
    {
        $expr = $this->parser->getExpressionParser()->parseExpression();

        // options
        if ($this->parser->getStream()->test(\Twig_Token::PUNCTUATION_TYPE, ',')) {
            $this->parser->getStream()->next();

            $options = $this->parser->getExpressionParser()->parseExpression();
        } else {
            $options = new \Twig_Node_Expression_Array(array(), $token->getLine());
        }

        $this->parser->getStream()->expect(\Twig_Token::BLOCK_END_TYPE);

        return new \GL\Core\TwigRouteNode($expr, $options, $token->getLine(), $this->getTag());
    }

    public function getTag()
    {
        return 'route';
    }
}
