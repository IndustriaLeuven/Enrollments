<?php

namespace PluginBundle\Form;

use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Form\Transformer\EntityToIdObjectTransformer;
use PluginBundle\Constraints\ExpressionLanguage;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\ReversedTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoleDifferentiationPluginConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('condition', TextType::class, [
                'label' => 'plugin.role_differentation.conf.rules.condition',
                'attr' => [
                    'help_text' => 'plugin.role_differentation.conf.rules.condition.help',
                ],
                'required' => false,
                'constraints' => [
                    new NotBlank(),
                    new ExpressionLanguage([
                        'expressionLanguage' => $options['expression_language'],
                        'variables' => ['token', 'user', 'roles'],
                    ]),
                ],
            ])
            ->add('target_form', EntityType::class, [
                'label' => 'plugin.role_differentation.conf.rules.target_form',
                'class' => 'AppBundle:Form',
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'plugin.role_differentation.conf.rules.target_form.deny_access',
            ])
            ->get('target_form')
            ->addModelTransformer(new IgnoreTransformErrorsTransformer(new ReversedTransformer(new EntityToIdObjectTransformer($options['em'], 'AppBundle:Form'))))
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
        $resolver->setRequired('expression_language')
            ->setAllowedTypes('expression_language', \Symfony\Component\ExpressionLanguage\ExpressionLanguage::class)
            ->setRequired('em')
            ->setAllowedTypes('em', EntityManager::class)
            ->setDefault('attr', ['style' => 'horizontal'])
            ;
    }
}
