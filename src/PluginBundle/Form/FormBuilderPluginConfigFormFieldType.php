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
                'label' => 'plugin.form_builder.conf.fields.name',
                'constraints' => [
                    new Regex([
                        'pattern' => '/^[a-z0-9_]+$/',
                        'message' => 'plugin.form_builder.conf.fields.name.constraints.characters',
                    ]),
                ],
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'plugin.form_builder.conf.fields.type',
                'choices' => [
                    'plugin.form_builder.conf.fields.type.choice.TextType' => TextType::class,
                    'plugin.form_builder.conf.fields.type.choice.EmailType' => EmailType::class,
                    'plugin.form_builder.conf.fields.type.choice.IntegerType' => IntegerType::class,
                    'plugin.form_builder.conf.fields.type.choice.UrlType' => UrlType::class,
                    'plugin.form_builder.conf.fields.type.choice.CheckboxType' => CheckboxType::class,
                    'plugin.form_builder.conf.fields.type.choice.ChoiceType' => ChoiceType::class,
                    'plugin.form_builder.conf.fields.type.choice.DateType' => DateType::class,
                    'plugin.form_builder.conf.fields.type.choice.DateTimeType' => DateTimeType::class,
                    'plugin.form_builder.conf.fields.type.choice.TimeType' => TimeType::class
                ],
                'choices_as_values' => true,
            ])
            ->add('show_in_enrollment_list', CheckboxType::class, [
                'label' => 'plugin.form_builder.conf.fields.show_in_enrollment_list',
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('required', CheckboxType::class, [
                'label' => 'plugin.form_builder.conf.fields.required',
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('disabled', CheckboxType::class, [
                'label' => 'plugin.form_builder.conf.fields.disabled',
                'required' => false,
                'attr' => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('options', TextAreaType::class, [
                'label' => 'plugin.form_builder.conf.fields.options',
                'constraints' => [
                    new NotBlank(),
                    new ExpressionLanguage([
                        'expressionLanguage' => $options['options_expressionLanguage'],
                        'variables' => [],
                    ])
                ],
                'empty_data' => '{}',
                'attr' => [
                    'help_text' => 'plugin.form_builder.conf.fields.options.help',
                ],
            ])
            ->add('constraints', BootstrapCollectionType::class, [
                'label' => 'plugin.form_builder.conf.fields.constraints',
                'allow_add' => true,
                'add_button_text' => 'plugin.form_builder.conf.fields.constraints.add_button',
                'allow_delete' => true,
                'delete_button_text' => 'plugin.form_builder.conf.fields.constraints.delete_button',
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
