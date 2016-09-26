<?php

namespace AppBundle\EventListener;

use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UIEvents;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AdminButtonsListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            UIEvents::FORM => ['addFormAdminButtons', -9000],
            UIEvents::SUCCESS => ['addEnrollmentAdminButtons', -9000],
        ];
    }

    public function addFormAdminButtons(SubmittedFormTemplateEvent $event)
    {
        $event->addTemplate(new TemplateReference('AppBundle', 'Admin/Form', 'adminButtons', 'html', 'twig'));
    }


    public function addEnrollmentAdminButtons(EnrollmentTemplateEvent $event)
    {
        $event->addTemplate(new TemplateReference('AppBundle', 'Admin/Enrollment', 'adminButtons', 'html', 'twig'));
    }
}
