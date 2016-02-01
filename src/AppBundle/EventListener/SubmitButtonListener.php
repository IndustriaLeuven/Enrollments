<?php

namespace AppBundle\EventListener;

use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SubmitButtonListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::BUILD => ['onFormBuild', -255]
        ];
    }

    public function onFormBuild(BuildFormEvent $event)
    {
        if(!$event->getFormBuilder()->has('actions')) {
            $event->getFormBuilder()->add('actions', 'form_actions');
        }
        $event->getFormBuilder()
            ->get('actions')
            ->add('submit', 'submit');
    }

}
