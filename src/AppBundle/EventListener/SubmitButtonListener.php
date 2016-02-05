<?php

namespace AppBundle\EventListener;

use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\FormEvents;
use Braincrafted\Bundle\BootstrapBundle\Form\Type\FormActionsType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

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
        if($event->getFormBuilder()->getDisabled())
            return;
        if(!$event->getFormBuilder()->has('actions')) {
            $event->getFormBuilder()->add('actions', FormActionsType::class);
        }
        $event->getFormBuilder()
            ->get('actions')
            ->add('submit', SubmitType::class);
    }

}
