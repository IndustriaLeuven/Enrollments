<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\Form as EForm;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin;
use AppBundle\Event\Form;
use AppBundle\Event\UI;
use AppBundle\Event\PluginEvents;
use AppBundle\Plugin\PluginInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PluginListener implements EventSubscriberInterface
{
    /**
     * @var PluginInterface
     */
    private $plugin;

    /**
     * PluginListener constructor.
     * @param PluginInterface $plugin
     */
    public function __construct(PluginInterface $plugin)
    {
        $this->plugin = $plugin;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::GET => 'getPlugin',
            PluginEvents::BUILD_FORM => 'buildPluginForm',
            PluginEvents::SUBMIT_FORM => 'submitPluginForm',
            AdminEvents::SHOW_FORM => 'showFormAdminConfig',
            FormEvents::BUILD => 'buildForm',
            FormEvents::SETDATA => 'setFormData',
            FormEvents::SUBMIT => 'submitForm',
        ];
    }

    private function getPluginData(EForm $form)
    {
        return $form->getPluginData()->get($this->plugin);
    }

    public function getPlugin(Plugin\GetPluginEvent $event)
    {
        $event->registerPlugin($this->plugin);
    }

    public function buildPluginForm(Plugin\BuildFormEvent $event)
    {
        $builder = $event->getFormBuilder()->add($this->plugin->getName(), 'form')->get($this->plugin->getName());
        $this->plugin->buildConfigurationForm($builder);
        if(!$event->isNew()) {
            $config = $event->getForm()->getPluginData()->get($this->plugin);
            $builder->setData($config);
        }
    }

    public function submitPluginForm(Plugin\SubmitFormEvent $event)
    {
        if($event->getType() !== $event::TYPE_DELETE) {
            $config = $event->getSubmittedForm()->get($this->plugin->getName())->getData();
            $event->getForm()->getPluginData()->set($this->plugin, $config);
        }
    }

    public function buildForm(Form\BuildFormEvent $event)
    {
        $this->plugin->buildForm($event->getFormBuilder(), $this->getPluginData($event->getForm()));
    }

    public function setFormData(Form\SetDataEvent $event)
    {
        $pluginData = $event->getEnrollment()?$event->getEnrollment()->getPluginData()->get($this->plugin):null;
        $this->plugin->preloadForm($event->getUserForm(), $pluginData, $this->getPluginData($event->getForm()));
    }

    public function submitForm(Form\SubmitFormEvent $event)
    {
        $pluginData = $this->plugin->handleForm($event->getSubmittedForm(), $this->getPluginData($event->getForm()));
        $event->getEnrollment()->getPluginData()->set($this->plugin, $pluginData);
    }

    public function showFormAdminConfig(UI\FormTemplateEvent $event)
    {
        $event->addTemplate($this->plugin->getTemplateReference("Admin/get"), ['pluginData'=>$this->getPluginData($event->getForm())]);
    }
}
