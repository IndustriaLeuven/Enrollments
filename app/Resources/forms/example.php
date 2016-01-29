<?php

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

return function(FormBuilderInterface $formBuilder)
{
    $formBuilder
        ->add('name', 'text', [
            'constraints' => [
                new NotBlank(),
                new Length(['min' => 3]),
            ]
        ])
        ->add('email', 'email', [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ]
        ])
        ->add('submit', 'submit')
    ;
};
