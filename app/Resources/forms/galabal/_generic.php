<?php

use AppBundle\Entity\Enrollment;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\MoneyType;
use PluginBundle\Form\FormDefinition;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

class GalabalFormDefinition extends FormDefinition
{
    public function buildConfigForm(FormBuilderInterface $formBuilder)
    {
        $formBuilder
            ->add('dinner_available', CheckboxType::class, [
                'required' => false
            ])
            ->add('party_price', MoneyType::class)
            ->add('dinner_price', MoneyType::class)
        ;
    }

    private function validateConfig(array $config)
    {
        if(!isset($config['dinner_available'], $config['party_price'], $config['dinner_price']))
            throw new \DomainException('Missing configuration for this form.');
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $config = [])
    {
        $this->validateConfig($config);
        parent::buildForm($formBuilder, $config);
    }

    public function handleSubmission(Form $form, Enrollment $enrollment, array $config = [])
    {
        $this->validateConfig($config);
        parent::handleSubmission($form, $enrollment, $config);
    }
}
