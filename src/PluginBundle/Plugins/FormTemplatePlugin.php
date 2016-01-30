<?php

namespace PluginBundle\Plugins;

use AppBundle\Form\FinderChoiceLoader;
use AppBundle\Plugin\PluginInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

class FormTemplatePlugin implements PluginInterface
{
    private $searchDirs;

    public function __construct(array $searchDirs)
    {
        $this->searchDirs = $searchDirs;
    }

    public function getName()
    {
        return 'form_template';
    }

    public function getTemplateReference($template)
    {
        return new TemplateReference('PluginBundle', 'FormTemplatePlugin', $template, 'html', 'twig');
    }

    public function buildConfigurationForm(FormBuilderInterface $formBuilder)
    {
        $formBuilder->add('formType', 'choice', [
            'choice_loader' => new FinderChoiceLoader(Finder::create()->in($this->searchDirs), '.php'),
        ]);
    }

    public function buildForm(FormBuilderInterface $formBuilder, array $configuration)
    {
        $callback = @include $configuration['formType'];
        if(!$callback)
            throw new FileNotFoundException('File '.$configuration['formType'].' does not exist.');
        if(!is_callable($callback))
            throw new \BadFunctionCallException('Callback returned from '. $configuration['formType'].' is not callable.');
        $callback($formBuilder);
    }

    public function preloadForm(Form $form, $formData, array $configuration)
    {
        $form->setData($formData);
    }

    public function handleForm(Form $submittedForm, array $configuration)
    {
        return $submittedForm->getData();
    }
}
