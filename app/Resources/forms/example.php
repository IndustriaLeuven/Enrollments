<?php

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

return function(FormBuilderInterface $formBuilder)
{
    $formBuilder
        ->add('name', TextType::class, [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 3]),
            ]
        ])
        ->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ]
        ])
    ;
};
