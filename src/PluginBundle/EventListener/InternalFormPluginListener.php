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
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Protects a form from being accessed directly through the public interface
 *
 * Accesses through indirection (RoleDifferentiation or DivertEnrollments) is allowed,
 * and so is viewing enrollments on the form.
 */
class InternalFormPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'internal_form';
    use PluginConfigurationHelperTrait;

    /**
     * Indicates if it is the first time the UIEvents::FORM event was caught in this request
     * If it was the first time, it was the root form which we want to protect with this plugin.
     * @var bool
     */
    private $isRootForm = true;

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            UIEvents::FORM => ['onUiForm', 9001], // It's over ninethousand! Has to be before all plugins that might change the form
        ];
    }

    public function onPluginBuildForm(PluginBuildFormEvent $event)
    {
        $this->buildPluginForm($event, self::PLUGIN_NAME, false);
    }

    public function onPluginSubmitForm(PluginSubmitFormEvent $event)
    {
        $this->submitPluginForm($event, self::PLUGIN_NAME);
    }

    public function onAdminShowForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'InternalFormPlugin', 'Admin/get', 'html', 'twig'), [
            'pluginData' => $event->getForm()->getPluginData()->get(self::PLUGIN_NAME),
        ]);
    }

    public function onUiForm(SubmittedFormTemplateEvent $event)
    {
        if(!$this->isRootForm)
            return;
        $this->isRootForm = false;
        if($event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            throw new NotFoundHttpException('AppBundle\Entity\Form object not found.', new AccessDeniedHttpException('Attempt to access form marked as internal.'));
    }
}
