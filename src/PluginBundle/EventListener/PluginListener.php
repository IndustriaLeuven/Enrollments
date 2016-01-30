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

    private function isPluginEnabled(EForm $form)
    {
        return $form->getPluginData()->has($this->plugin);
    }

    public function getPlugin(Plugin\GetPluginEvent $event)
    {
        $event->registerPlugin($this->plugin);
    }

    public function buildPluginForm(Plugin\BuildFormEvent $event)
    {
        $builder = $event->getFormBuilder()
            ->add($this->plugin->getName(), 'fieldset', [
                'legend' => ucfirst(trim(strtolower(preg_replace(array('/([A-Z])/', '/[_\s]+/'), array('_$1', ' '), $this->plugin->getName())))),
                'label' => false,
            ])
            ->get($this->plugin->getName())
            ->add('enable', 'checkbox', [
                'label' => 'Enable',
                'required' => false,
                'data' => !$event->isNew()&&!$this->isPluginEnabled($event->getForm()),
            ]);
        $dataBuilder = $builder
            ->add('data', 'form', [
                'label' => false,
            ])
            ->get('data');
        $this->plugin->buildConfigurationForm($dataBuilder);
        if(!$event->isNew()) {
            $config = $event->getForm()->getPluginData()->get($this->plugin);
            $dataBuilder->setData($config);
            $builder->get('enable')->setData($this->isPluginEnabled($event->getForm()));
        }
    }

    public function submitPluginForm(Plugin\SubmitFormEvent $event)
    {
        if($event->getType() !== $event::TYPE_DELETE) {
            if($event->getSubmittedForm()->get($this->plugin->getName())->get('enable')->getData()) {
                $config = $event->getSubmittedForm()->get($this->plugin->getName())->get('data')->getData();
                $event->getForm()->getPluginData()->set($this->plugin, $config);
            } else {
                $event->getForm()->getPluginData()->remove($this->plugin);
            }
        }
    }

    public function buildForm(Form\BuildFormEvent $event)
    {
        if($this->isPluginEnabled($event->getForm())) {
            $this->plugin->buildForm($event->getFormBuilder(), $this->getPluginData($event->getForm()));
        }
    }

    public function setFormData(Form\SetDataEvent $event)
    {
        if($this->isPluginEnabled($event->getForm())) {
            $pluginData = $event->getEnrollment() ? $event->getEnrollment()->getPluginData()->get($this->plugin) : null;
            $this->plugin->preloadForm($event->getUserForm(), $pluginData, $this->getPluginData($event->getForm()));
        }
    }

    public function submitForm(Form\SubmitFormEvent $event)
    {
        if($this->isPluginEnabled($event->getForm())) {
            $pluginData = $this->plugin->handleForm($event->getSubmittedForm(), $this->getPluginData($event->getForm()));
            $event->getEnrollment()->getPluginData()->set($this->plugin, $pluginData);
        }
    }

    public function showFormAdminConfig(UI\FormTemplateEvent $event)
    {
        if($this->isPluginEnabled($event->getForm())) {
            $event->addTemplate($this->plugin->getTemplateReference("Admin/get"), ['pluginData' => $this->getPluginData($event->getForm())]);
        }
    }
}
