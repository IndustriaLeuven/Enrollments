<?php

namespace PluginBundle\Constraints;

use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ExpressionLanguageValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint ExpressionLanguage */
        if(!$value)
            return;

        try {
            $constraint->expressionLanguage->parse($value, $constraint->variables);
        } catch(SyntaxError $error) {
            $this->context->addViolation($error->getMessage());
        }
    }
}
