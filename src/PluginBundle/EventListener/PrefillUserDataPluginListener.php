<?php

namespace PluginBundle\EventListener;

use AppBundle\Entity\User;
use AppBundle\Event\AdminEvents;
use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\Form\SetDataEvent;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\Plugin\PluginBuildFormEvent;
use AppBundle\Event\Plugin\PluginSubmitFormEvent;
use AppBundle\Event\PluginEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use GuzzleHttp\Client;
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
     * @var Client
     */
    private $authserverClient;

    /**
     * PrefillUserDataPluginListener constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param Client $authserverClient
     */
    public function __construct(TokenStorageInterface $tokenStorage, Client $authserverClient)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authserverClient = $authserverClient;
    }

    public static function getSubscribedEvents()
    {
        return [
            PluginEvents::BUILD_FORM => 'onPluginBuildForm',
            PluginEvents::SUBMIT_FORM => 'onPluginSubmitForm',
            AdminEvents::FORM_GET => 'onAdminShowForm',
            FormEvents::BUILD => 'onFormBuild',
            FormEvents::SUBMIT => 'onFormSubmit',
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
        if(!($user = $this->getUser()))
            return;
        if($event->getFormBuilder()->has('name'))
            $event->getFormBuilder()->get('name')
                ->setDisabled(true)
                ->setData($user->getRealname());
        if($event->getFormBuilder()->has('email')) {
            $userData = $this->getAuthserverUserData();
            if($userData && isset($userData['emails'])) {
                $primaryEmail = array_filter($userData['emails'], function($email) {
                    return $email['primary']&&$email['verified'];
                });
                if(isset($primaryEmail[0]))
                    $event->getFormBuilder()->get('email')->setEmptyData($primaryEmail[0]['addr']);
            }
        }
    }

    public function onFormSubmit(SubmitFormEvent $event)
    {
        if(!$event->getForm()->getPluginData()->has(self::PLUGIN_NAME))
            return;
        if(!($user = $this->getUser()))
            return;
        if($event->getSubmittedForm()->has('name'))
            $event->getEnrollment()->setData($event->getEnrollment()->getData()+['name' => $user->getRealname()]);
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
     * @return array|null
     */
    private function getAuthserverUserData()
    {
        if(($user = $this->getUser()))
            return json_decode($this->authserverClient->get('admin/users/'.$user->getAuthId().'.json')->getBody(), true);
        return null;
    }
}
