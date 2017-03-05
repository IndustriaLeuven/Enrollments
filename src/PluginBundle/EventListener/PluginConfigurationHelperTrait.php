<?php

namespace PluginBundle\EventListener;

use AdamQuaile\Bundle\FieldsetBundle\Form\FieldsetType;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;

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
            ->add($name, FieldsetType::class, [
                'legend' => 'plugin.'.$name.'.title',
                'attr' => [
                    'doc_page' => $name.'.md',
                ],
                'virtual' => false,
                'mapped' => false,
                'label' => false,
                'validation_groups' => function(FormInterface $form) {
                    return $form->get('enable')->getData()?['Default']:false;
                }
            ])
            ->get($name)
            ->add('enable', CheckboxType::class, [
                'label' => 'plugin.label.enabled',
                'required' => false,
                'data' => !$event->isNew()&&$event->getForm()->getPluginData()->has($name),
            ]);
        if(!$isConfigurable)
            return null;
        return $builder
            ->add('data', FormType::class, [
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
