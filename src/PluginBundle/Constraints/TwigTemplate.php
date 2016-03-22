<?php

namespace PluginBundle\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * {@inheritDoc}
 */
class TwigTemplate extends Constraint
{
    /**
     * @var \Twig_Environment
     */
    public $twig;

    public function getRequiredOptions()
    {
        return [
            'twig',
        ];
    }

}
