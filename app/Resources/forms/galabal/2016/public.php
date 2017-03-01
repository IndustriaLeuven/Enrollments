<?php

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

return function(FormBuilderInterface $formBuilder)
{
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
            'data' => true,
            'disabled' => true,
            'label' => 'Party&emsp;&mdash;&emsp;&euro;17',
        ])
        ->add('diner', CheckboxType::class, [
            'label' => 'Diner&emsp;(Sold out!)',
            'required' => false,
            'data' => false,
            'disabled' => true,
        ])
    ;
};
