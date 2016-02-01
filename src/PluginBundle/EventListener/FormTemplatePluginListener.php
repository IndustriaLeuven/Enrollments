<?php

namespace PluginBundle\EventListener;

use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\FormTemplateEvent;
use AppBundle\Form\FinderChoiceLoader;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Finder\Finder;

class FormTemplatePluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'form_template';
    use PluginConfigurationHelperTrait;

    /**
     * @var string
     */
    private $searchDir;

    /**
     * FormTemplatePluginListener constructor.
     * @param string $searchDir
     */
    public function __construct($searchDir)
    {
        $this->searchDir = $searchDir;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::SHOW_FORM => 'onAdminShowForm',
            FormEvents::BUILD => 'onFormBuild',
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('formType', 'choice', [
                'choice_loader' => new FinderChoiceLoader(Finder::create()->files()->in($this->searchDir), '.php'),
            ]);
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onAdminShowForm(FormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'FormTemplatePlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);

    }

    public function onFormBuild(BuildFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $configuration = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        $callback = @include $this->searchDir.'/'.$configuration['formType'];
        if(!$callback)
            throw new FileNotFoundException('File '.$configuration['formType'].' does not exist.');
        if(!is_callable($callback))
            throw new \BadFunctionCallException('Callback returned from '. $configuration['formType'].' is not callable.');
        $callback($event->getFormBuilder());
    }
}
