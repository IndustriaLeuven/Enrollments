<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\Form\SetDataEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;

class DefaultEventsListener implements EventSubscriberInterface
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * DefaultEventsListener constructor.
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    public static function getSubscribedEvents()
    {
        return [
            UIEvents::FORM => 'uiFormEvent',
            UIEvents::SUCCESS => 'uiSuccessEvent'
        ];
    }

    private function createForm(EventDispatcherInterface $eventDispatcher, FormBuilderInterface $formBuilder, Form $formEntity, Enrollment $enrollment = null)
    {
        if($enrollment)
            $formBuilder->setData($enrollment->getData());
        $buildFormEvent = new BuildFormEvent($formEntity, $formBuilder);
        $eventDispatcher->dispatch(FormEvents::BUILD, $buildFormEvent);
        $form = $buildFormEvent->getFormBuilder()->getForm();
        $eventDispatcher->dispatch(FormEvents::SETDATA, new SetDataEvent($formEntity, $form, $enrollment));
        return $form;
    }

    public function uiFormEvent(SubmittedFormTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $form = $this->createForm($eventDispatcher, $this->formFactory->createBuilder(), $event->getForm());
        $event->addTemplate(new TemplateReference('AppBundle', 'Enrollment', 'form', 'html', 'twig'), ['form'=>$form]);
        $event->setSubmittedForm($form);
    }

    public function uiSuccessEvent(EnrollmentTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $form = $this->createForm($eventDispatcher, $this->formFactory->createBuilder()->setDisabled(true), $event->getForm(), $event->getEnrollment());
        if(!$event->getTemplates()->count())
            $event->addTemplate(new TemplateReference('AppBundle', 'Enrollment', 'success', 'html', 'twig'), ['form' => $form]);
    }
}
