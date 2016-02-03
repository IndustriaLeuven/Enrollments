<?php

namespace AppBundle\EventListener;

use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UIEvents;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminButtonsListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            UIEvents::FORM => ['addAdminButtons', -255]
        ];
    }

    public function addAdminButtons(SubmittedFormTemplateEvent $event)
    {
        $event->addTemplate(new TemplateReference('AppBundle', 'Admin/Form', 'adminButtons', 'html', 'twig'));
    }

}