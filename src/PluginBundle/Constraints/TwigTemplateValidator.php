<?php

namespace PluginBundle\Constraints;

use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class TwigTemplateValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed $value The value that should be validated
     * @param Constraint $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint TwigTemplate */
        if(!$value)
            return;

        try {
            $constraint->twig->createTemplate($value);
        } catch(\Twig_Error_Syntax $error) {
            $this->context->addViolation($error->getMessage());
        }
    }
}
