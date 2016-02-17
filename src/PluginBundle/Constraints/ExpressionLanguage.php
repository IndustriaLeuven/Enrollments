<?php

namespace PluginBundle\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * {@inheritDoc}
 */
class ExpressionLanguage extends Constraint
{
    /**
     * @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    public $expressionLanguage;

    /**
     * @var string[]
     */
    public $variables;

    public function getRequiredOptions()
    {
        return [
            'expressionLanguage',
            'variables',
        ];
    }

}
