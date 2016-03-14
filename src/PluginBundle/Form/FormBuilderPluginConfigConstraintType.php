<?php

namespace PluginBundle\Form;

use PluginBundle\Constraints\ExpressionLanguage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;

class FormBuilderPluginConfigConstraintType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'NotBlank' => NotBlank::class,
                    'Blank' => Blank::class,
                    'Email' => Email::class,
                    'Length' => Length::class,
                    'Url' => Url::class,
                    'Regex' => Regex::class,
                    'Choice' => Choice::class,
                    'Range' => Range::class,
                    'EqualTo' => EqualTo::class,
                    'NotEqualTo' => NotEqualTo::class,
                    'LessThan' => LessThan::class,
                    'LessThanOrEqual' => LessThanOrEqual::class,
                    'GreaterThan' => GreaterThan::class,
                    'GreaterThanOrEqual' => GreaterThanOrEqual::class,
                ],
                'choices_as_values' => true,
            ])
            ->add('options', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new ExpressionLanguage([
                        'expressionLanguage' => $options['options_expressionLanguage'],
                        'variables' => [],
                    ])
                ],
                'data' => 'null',
                'attr' => [
                    'help_text' => 'Expression for the options for the validator.<br>'.
                        'For applicable options for each constraint, see <a href="https://symfony.com/doc/2.8/reference/constraints.html">validation constraint reference</a>',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('options_expressionLanguage')
            ->setAllowedTypes('options_expressionLanguage', \Symfony\Component\ExpressionLanguage\ExpressionLanguage::class)
        ;
    }

}
