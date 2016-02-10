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
            'attr' => [
                'data-pricing-reload' => true,
            ],
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
        ->add('events', FormType::class, [
            'label' => false,
        ])
        ->get('events')
        ->add('party', CheckboxType::class, [
            'data' => true,
            'disabled' => true,
            'label' => 'Party&emsp;&mdash;&emsp;&euro;17',
            'attr' => [
                'data-pricing-reload' => true,
            ],
        ])
        ->add('diner', CheckboxType::class, [
            'label' => 'Diner&emsp;&mdash;&emsp;&euro;38',
            'required' => false,
            'attr' => [
                'data-pricing-reload' => true,
            ],
        ])
    ;
};
