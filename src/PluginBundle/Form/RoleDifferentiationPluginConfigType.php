<?php

namespace PluginBundle\Form;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Form\Transformer\EntityToIdObjectTransformer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoleDifferentiationPluginConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('condition', 'text', [
                'attr' => [
                    'help_text' => "Available functions: is_anonymous(), is_authenticated(), is_fully_authenticated(), has_role(role)\n".
                        "Available variables: token, user, roles",
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    $options['expression_language_validator'],
                ],
            ])
            ->add('target_form', EntityType::class, [
                'class' => 'AppBundle:Form',
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Deny access',
            ])
            ->get('target_form')
            ->addModelTransformer(new ReversedTransformer(new EntityToIdObjectTransformer($options['em'], 'AppBundle:Form')))
            ->addModelTransformer(new CallbackTransformer(function($object) {
                if($object)
                    return ['id' => $object];
                return null;
            }, function($object) {
                return $object;
            }))
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired('expression_language_validator')
            ->setAllowedTypes('expression_language_validator', Callback::class)
            ->setRequired('em')
            ->setAllowedTypes('em', EntityManager::class)
            ->setDefault('attr', ['style' => 'horizontal'])
            ;
    }
}