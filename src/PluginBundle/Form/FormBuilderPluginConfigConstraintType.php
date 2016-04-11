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
                'label' => 'plugin.form_builder.conf.fields.constraints.type',
                'choices' => [
                    'plugin.form_builder.conf.fields.constraints.type.choice.NotBlank' => NotBlank::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.Blank' => Blank::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.Email' => Email::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.Length' => Length::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.Url' => Url::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.Regex' => Regex::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.Choice' => Choice::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.Range' => Range::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.EqualTo' => EqualTo::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.NotEqualTo' => NotEqualTo::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.LessThan' => LessThan::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.LessThanOrEqual' => LessThanOrEqual::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.GreaterThan' => GreaterThan::class,
                    'plugin.form_builder.conf.fields.constraints.type.choice.GreaterThanOrEqual' => GreaterThanOrEqual::class,
                ],
                'choices_as_values' => true,
            ])
            ->add('options', TextType::class, [
                'label' => 'plugin.form_builder.conf.fields.constraints.options',
                'constraints' => [
                    new NotBlank(),
                    new ExpressionLanguage([
                        'expressionLanguage' => $options['options_expressionLanguage'],
                        'variables' => [],
                    ])
                ],
                'empty_data' => 'null',
                'attr' => [
                    'help_text' => 'plugin.form_builder.conf.fields.constraints.options.help',
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
