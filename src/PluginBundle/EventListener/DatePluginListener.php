<?php

namespace PluginBundle\EventListener;

use AppBundle\Event\AdminEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UIEvents;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotNull;

class DatePluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'date';
    use PluginConfigurationHelperTrait;
    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => ['onAdminShowForm', 10],
            UIEvents::FORM => ['onUIForm', 200],
            UIEvents::SUCCESS => ['onUIForm', 200],
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME)
            ->add('startDate', DateType::class, [
                'required' => false,
                'constraints' => [
                    new NotNull(),
                ]
            ])
            ->add('endDate', DateType::class, [
                'required' => false,
            ])
        ;
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onUIForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $pluginData = $event->getForm()->getPluginData()->get(self::PLUGIN_NAME);
        if($pluginData['startDate'] > new \DateTime())
            $event->addTemplate(new TemplateReference('PluginBundle', 'DatePlugin', 'UI/Form', 'html', 'twig'), [
                'toSoon' => true,
            ])->stopPropagation();
        if($pluginData['endDate'] !== null&&$pluginData['endDate'] < new \DateTime())
            $event->addTemplate(new TemplateReference('PluginBundle', 'DatePlugin', 'UI/Form', 'html', 'twig'), [
                'toSoon' => false,
            ])->stopPropagation();
    }

    public function onAdminShowForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'DatePlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

}