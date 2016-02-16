<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\Form\SetDataEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PrefillUserDataPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME = 'prefill_user_data';
    use PluginConfigurationHelperTrait;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * PrefillUserDataPluginListener constructor.
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            FormEvents::BUILD => 'onFormBuild',
            FormEvents::SETDATA => 'onFormSetData',
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
        $event->addTemplate(new TemplateReference('PluginBundle', 'PrefillUserDataPlugin', 'Admin/get', 'html', 'twig'));
    }

    public function onFormBuild(BuildFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        if(!($token = $this->tokenStorage->getToken()))
            return;
        /* @var $token TokenInterface */
        if(!($user = $token->getUser()))
            return;
        /* @var $user User */
        if($event->getFormBuilder()->has('name'))
            $event->getFormBuilder()->get('name')->setDisabled(true);
    }

    public function onFormSetData(SetDataEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        if(!($token = $this->tokenStorage->getToken()))
            return;
        /* @var $token TokenInterface */
        if(!($user = $token->getUser()))
            return;
        /* @var $user User */
        if($event->getSubmittedForm()->has('name'))
            $event->getSubmittedForm()->get('name')->setData($user->getRealname());
        if(!$event->getSubmittedForm()->has('email')&&$user->getEmail())
            $event->getSubmittedForm()->get('email')->setData($user->getEmail());
    }
}
