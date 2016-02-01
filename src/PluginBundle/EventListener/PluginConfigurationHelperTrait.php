<?php

namespace PluginBundle\EventListener;

use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;

trait PluginConfigurationHelperTrait
{
    /**
     * Creates a basic plugin configuration form
     * @param PluginBuildFormEvent $event
     * @param string $name The name of the plugin
     * @param bool $isConfigurable If the plugin is configurable
     * @return \Symfony\Component\Form\FormBuilderInterface|null A formbuilder to add all plugin options to, is $isConfigurable = true, else null
     */
    private function buildPluginForm(PluginBuildFormEvent $event, $name, $isConfigurable = true)
    {
        $builder = $event->getFormBuilder()
            ->add($name, 'fieldset', [
                'legend' => ucfirst(trim(strtolower(preg_replace(array('/([A-Z])/', '/[_\s]+/'), array('_$1', ' '), $name)))),
                'label' => false,
            ])
            ->get($name)
            ->add('enable', 'checkbox', [
                'label' => 'Enable',
                'required' => false,
                'data' => !$event->isNew()&&$event->getForm()->getPluginData()->has($name),
            ]);
        if(!$isConfigurable)
            return null;
        return $builder
            ->add('data', 'form', [
                'label' => false,
                'data' => $event->isNew()?null:$event->getForm()->getPluginData()->get($name),
            ])
            ->get('data');
    }

    /**
     * Handles updating of plugin configuration on plugin configuration form submission
     * @param PluginSubmitFormEvent $event
     * @param string $name
     */
    private function submitPluginForm(PluginSubmitFormEvent $event, $name)
    {
        if($event->getType() === $event::TYPE_DELETE)
            return;
        $submittedPluginForm = $event->getSubmittedForm()->get($name);
        $pluginDataBag = $event->getForm()->getPluginData();
        if(!$submittedPluginForm->get('enable')->getData()) {
            $pluginDataBag->remove($name);
            return;
        }
        if(!$submittedPluginForm->has('data')) {
            $pluginDataBag->set($name, true);
            return;
        }
        $pluginDataBag->set($name, $submittedPluginForm->get('data')->getData());
    }

}
