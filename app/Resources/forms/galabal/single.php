<?php

require __DIR__.'/_generic.php';

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

return new GalabalFormDefinition(function(FormBuilderInterface $formBuilder, $settings) {
    $formBuilder
        ->add('name', TextType::class, [
            'constraints' => [
                new NotBlank()
            ],
        ])
        ->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
        ])
        ->add('vegetarian', CheckboxType::class, [
            'label' => 'Vegetarian',
            'required' => false,
        ])
        ->add('events', FormType::class, [
            'label' => false,
        ])
        ->get('events')
        ->add('party', CheckboxType::class, [
            'label' => 'Party&emsp;&mdash;&emsp;'.$settings['party_price'],
            'data' => true,
            'disabled' => true,
        ])
        ->add('diner', CheckboxType::class, [
            'label' => 'Diner&emsp;&mdash;&emsp;'.$settings['dinner_price'],
            'required' => false,
            'disabled' => !$settings['dinner_available'],
            'attr' => [
                'data-onload' => 'onchange',
                'onchange' => '$(this).prop("checked")?$("#form_vegetarian").prop("disabled", false):$("#form_vegetarian").prop("disabled", true).prop("checked", false)',
            ],
        ]);
    if($settings['show_reception'])
        $formBuilder->get('events')->add('reception', CheckboxType::class, [
            'label' => 'Reception&emsp;&mdash;&emsp;Free',
            'required' => false,
        ]);
});
