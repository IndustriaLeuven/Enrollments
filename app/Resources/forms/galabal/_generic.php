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
            ->add('show_reception', CheckboxType::class, [
                'required' => false
            ])
            ->add('party_price', MoneyType::class)
            ->add('dinner_price', MoneyType::class)
        ;
    }

    private function validateConfig(array $config)
    {
        if(!isset($config['dinner_available'], $config['show_reception'], $config['party_price'], $config['dinner_price']))
            throw new \DomainException('Missing configuration for this form.');
    }

    private function normalizeConfig(array &$config)
    {
        foreach(['party_price', 'dinner_price'] as $k) {
            if($config[$k] == 0) {
                $config[$k] = 'Free';
            } else {
                $config[$k] = '&euro;'.$config[$k];
            }
        }
        if(!$config['dinner_available'])
            $config['dinner_price'] = 'Sold out';
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $config = [])
    {
        $this->validateConfig($config);
        $this->normalizeConfig($config);
        parent::buildForm($formBuilder, $config);
    }

    public function handleSubmission(Form $form, Enrollment $enrollment, array $config = [])
    {
        $this->validateConfig($config);
        $this->normalizeConfig($config);
        parent::handleSubmission($form, $enrollment, $config);
    }
}
