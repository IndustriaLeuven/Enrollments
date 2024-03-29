<?php

use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

return function (FormBuilderInterface $formBuilder) {
    $formBuilder
        ->add('first_name', TextType::class, [
            'label' => 'Voornaam',
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('last_name', TextType::class, [
            'label' => 'Achternaam',
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('birthdate', BirthdayType::class, [
            'label' => 'Geboortedatum',
            'input' => 'string',
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('group', TextType::class, [
            'label' => 'Groep (als je die al weet)',
            'required' => false,
            'constraints' => [
                new Length(['max' => 3]),
            ],
        ])
        ->add('phone', TextType::class, [
            'label' => 'GSM of telefoon nr.',
            'constraints' => [
                new NotBlank(),
            ],
        ])
        ->add('email', EmailType::class, [
            'label' => 'Email adres',
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
        ])
        ->add('vegetarian', CheckboxType::class, [
            'label' => 'Vegetarisch',
            'required' => false,
        ])
        ->add('comments', TextareaType::class, [
            'label' => 'Opmerkingen (medicatie, ...)',
            'required' => false,
        ])
    ;
};
