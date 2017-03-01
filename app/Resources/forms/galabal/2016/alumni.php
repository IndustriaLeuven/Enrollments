<?php

use AppBundle\Entity\Enrollment;
use PluginBundle\Form\FormDefinition;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

return new FormDefinition(function(FormBuilderInterface $formBuilder)
{
    $formBuilder
        ->add('name', TextType::class)
        ->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ]
        ])
        ->add('vegetarian', CheckboxType::class, [
            'label' => 'Vegetarian',
            'required' => false,
        ])
        ->add('plus_one', CheckBoxType::class, [
            'label' => '+1',
            'required' => false,
            'attr' => [
                'data-onload' => 'onchange',
                'onchange' => '$(this).prop("checked")?$("#form_plus_one_data").show():$("#form_plus_one_data").hide()',
            ],
        ])
        ->add('plus_one_data', FormType::class, [
            'label' => false,
            'required' => false,
            'constraints' => [
                new Valid(),
            ],
            'attr' => [
                'style' => @$formBuilder->getData()['plus_one']?'':'display: none;'
            ],
        ])
        ->add('events', FormType::class, [
            'label' => false,
        ])
    ;

    $formBuilder->get('plus_one_data')
        ->add('name', TextType::class)
        ->add('email', EmailType::class, [
            'required' => false,
            'constraints' => [
                new Email(),
            ],
        ])
        ->add('vegetarian', CheckboxType::class, [
            'label' => 'Vegetarian',
            'required' => false,
        ])
    ;

    $formBuilder->get('events')
        ->add('party', CheckboxType::class, [
            'data' => true,
            'disabled' => true,
            'label' => 'Party&emsp;&mdash;&emsp;&euro;15',
        ])
        ->add('diner', CheckboxType::class, [
            'label' => 'Diner&emsp;(Sold out!)',
            'required' => false,
            'data' => false,
            'disabled' => true,
        ])
        ->add('reception', CheckboxType::class, [
            'label' => 'Reception&emsp;&mdash;&emsp;Free',
            'required' => false,
        ]);
}, function(Form $form, Enrollment $enrollment) {
    $data = $enrollment->getData();
    if(!isset($data['events']))
        $data['events'] = [];
    $data['events']['party'] = true;
    if($form->get('events')->get('diner')->getData()) {
        $data['events']['reception'] = true;
    }

    if($form->get('plus_one')->getData()&&!$form->get('plus_one_data')->get('name')->getData()) {
        $form->get('plus_one_data')->get('name')->addError(new FormError('Name of your +1 is required.'));
    }
    if(!$form->get('plus_one')->getData())
        unset($data['plus_one_data']);
    $enrollment->setData($data);
});
