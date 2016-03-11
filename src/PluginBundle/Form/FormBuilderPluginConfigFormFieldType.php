<?php

namespace PluginBundle\Form;


use Braincrafted\Bundle\BootstrapBundle\Form\Type\BootstrapCollectionType;
use PluginBundle\Constraints\ExpressionLanguage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class FormBuilderPluginConfigFormFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-z0-9_]+$/',
                        'message' => 'This value may only contain lowercase characters, numbers and underscores.',
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Text' => TextType::class,
                    'Email' => EmailType::class,
                    'Integer' => IntegerType::class,
                    'Url' => UrlType::class,
                    'Checkbox' => CheckboxType::class,
                    'Choice' => ChoiceType::class,
                    'Date' => DateType::class,
                    'DateTime' => DateTimeType::class,
                    'Time' => TimeType::class
                ],
                'choices_as_values' => true,
                'required' => false,
            ])
            ->add('show_in_enrollment_list', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('required', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('disabled', CheckboxType::class, [
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('options', TextAreaType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new ExpressionLanguage([
                        'expressionLanguage' => $options['options_expressionLanguage'],
                        'variables' => [],
                    ])
                ],
                'data' => '{}',
                'attr' => [
                    'help_text' => 'Expression for the options for the field.<br>'.
                        'For applicable options for each field, see <a href="http://symfony.com/doc/2.8/reference/forms/types.html">form types reference</a>',
                ],
            ])
            ->add('constraints', BootstrapCollectionType::class, [
                'allow_add' => true,
                'add_button_text' => 'Add constraint',
                'allow_delete' => true,
                'delete_button_text' => 'Remove constraint',
                'required' => false,
                'prototype_name' => '__constraints_proto__',
                'type' => FormBuilderPluginConfigConstraintType::class,
                'options' => [
                    'options_expressionLanguage' => $options['constraints_expressionLanguage'],
                    'attr' => [
                        'style' => 'horizontal',
                    ],
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired('constraints_expressionLanguage')
            ->setAllowedTypes('constraints_expressionLanguage', \Symfony\Component\ExpressionLanguage\ExpressionLanguage::class)
            ->setRequired('options_expressionLanguage')
            ->setAllowedTypes('options_expressionLanguage', \Symfony\Component\ExpressionLanguage\ExpressionLanguage::class)
        ;
    }

}
