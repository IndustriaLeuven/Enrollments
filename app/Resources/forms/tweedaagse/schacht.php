<?php

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
new Symfony\Component\Validator\Constraints\Length;

return function(FormBuilderInterface $formBuilder)
{
    $formBuilder
        ->add('first_name', TextType::class, ['label'=>'Voornaam',
            'constraints' => [new NotBlank()],
        ])

        ->add('last_name', TextType::class, ['label'=>'Achternaam',
            'constraints' => [new NotBlank()],
        ])

        ->add('Birthdate', DateType::class,['label'=>'Geboortedatum',
        'constraints'=>[new NotBlank()],
        ])

        ->add('group',TextType::class,['label'=>'Groep (als je die al weet)','required' => false,
            'constraints'=>[
                new Length(['max'=>3])
            ],
        ])

        ->add('phone',TextType::class,['label'=>'GSM of telefoon nr.',
        'constraints'=>[new NotBlank()],
        ])

        ->add('email', EmailType::class, ['label'=>'email',
            'constraints' => [
                new NotBlank(),
                new Email()
            ],
        ])

        ->add('vegetarian', CheckboxType::class, ['label'=>'Vegetarisch',
            'required' => false,
        ])

        ->add('Comments', TextareaType::class,['label'=>'Opmerkingen (medicatie, ...', 'required'=> false,
            'constraints'=>[
                new NotBlank(),
        ],
        ])

    ;
};
