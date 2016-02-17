<?php

namespace PluginBundle\ExpressionLanguage;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

class LogicExpressionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @return ExpressionFunction[] An array of Function instances
     */
    public function getFunctions()
    {
        return [
            new ExpressionFunction('if', function($condition, $trueExpr, $falseExpr) {
                return "(($condition)?($trueExpr):($falseExpr))";
            }, function($vars, $condition, $trueExpr, $falseExpr) {
                return $condition?$trueExpr:$falseExpr;
            }),
        ];
    }
}
