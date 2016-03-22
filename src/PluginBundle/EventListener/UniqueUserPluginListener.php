<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Event\AbstractFormEvent;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UIEvents;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class UniqueUserPluginListener implements EventSubscriberInterface
{
    const PLUGIN_NAME='unique_user';
    use PluginConfigurationHelperTrait;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * UniqueUserPluginListener constructor.
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
            FormEvents::SUBMIT => ['onFormSubmit', 11], // Before UniqueFieldsPlugin
            UIEvents::FORM => ['onUIForm', 5],
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

    public function onUIForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        if(!$this->getUser())
            return;

        if($this->hasEnrollments($event)) {
            $event->addTemplate(new TemplateReference('PluginBundle', 'UniqueUserPlugin', 'UI/form', 'html', 'twig'), [
                'enrollments' => $this->getEnrollments($event),
            ]);
            $event->stopPropagation();
        }
    }

    public function onAdminShowForm(SubmittedFormTemplateEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        $event->addTemplate(new TemplateReference('PluginBundle', 'UniqueUserPlugin', 'Admin/get', 'html', 'twig'));
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;

        if(!$this->getUser())
            return;

        if($this->hasEnrollments($event)) {
            $event->getSubmittedForm()
                ->addError(new FormError('This form can only be submitted once per user'));
        }
    }
    /**
     * @return User|null
     */
    public function getUser()
    {
        if(!($token = $this->tokenStorage->getToken()))
            return null;
        /* @var $token TokenInterface */
        $user = $token->getUser();
        if($user instanceof User)
            return $user;
        return null;
    }

    /**
     * @param AbstractFormEvent $event
     * @return bool
     */
    private function hasEnrollments(AbstractFormEvent $event)
    {
        return $this->getEnrollments($event)->count() > 0;
    }

    /**
     * @param AbstractFormEvent $event
     * @return \Doctrine\Common\Collections\Collection
     */
    private function getEnrollments(AbstractFormEvent $event)
    {
        return $event->getForm()->getEnrollments()->matching(Criteria::create()->where(
            Criteria::expr()->eq('createdBy', $this->getUser())
        ));
    }
}
