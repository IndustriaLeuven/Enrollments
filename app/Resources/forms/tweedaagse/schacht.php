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
        ->add('First Name*', TextType::class, [
            'constraints' => [
                new NotBlank()
            ],
        ])

        ->add('Last Name*', TextType::class, [
            'constraints' => [
                new NotBlank()
            ],
        ])

        ->add('Group (if you already know',TextType::class,[
            'constraints'=>[
                new max(Length(3)),
            ],
        ])

        ->add('GSM or Phone',TextType::class)

        ->add('email*', EmailType::class, [
            'constraints' => [
                new NotBlank(),
                new Email(),
            ],
        ])

        ->add('Comments',TextareaType::class)

    ;
};
