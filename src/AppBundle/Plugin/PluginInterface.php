<?php

namespace AppBundle\Plugin;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

interface PluginInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @return TemplateReference
     */
    public function getTemplateReference($template);

    /**
     * @param FormBuilderInterface $formBuilder
     * @return void
     */
    public function buildConfigurationForm(FormBuilderInterface $formBuilder);

    /**
     * @param FormBuilderInterface $formBuilder
     * @param array $configuration
     * @return void
     */
    public function buildForm(FormBuilderInterface $formBuilder, array $configuration);

    /**
     * @param Form $submittedForm
     * @param array $configuration
     * @return mixed
     */
    public function handleForm(Form $submittedForm, array $configuration);

    /**
     * @param Form $form
     * @param mixed $formData
     * @param array $configuration
     * @return void
     */
    public function preloadForm(Form $form, $formData,array $configuration);
}
