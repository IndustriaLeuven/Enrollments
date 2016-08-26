<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\Form\BuildFormEvent;
use AppBundle\Event\Form\SetDataEvent;
use AppBundle\Event\Form\SubmitFormEvent;
use AppBundle\Event\FormEvents;
use AppBundle\Event\UI\SubmittedFormTemplateEvent;
use AppBundle\Event\UI\EnrollmentTemplateEvent;
use AppBundle\Event\UIEvents;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\ButtonBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

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
            UIEvents::SUCCESS => 'uiSuccessEvent',
            FormEvents::SUBMIT => ['formSubmitEvent', 9001], // It's over ninethousand. This is a patch-up event handler that needs to be run before any other plugins
        ];
    }

    private function createForm(EventDispatcherInterface $eventDispatcher, FormBuilderInterface $formBuilder, Form $formEntity, Enrollment $enrollment = null)
    {
        if($enrollment)
            $formBuilder->setData($enrollment->getData());
        $buildFormEvent = new BuildFormEvent($formEntity, $formBuilder);
        $eventDispatcher->dispatch(FormEvents::BUILD, $buildFormEvent);
        if($enrollment) {
            // Remove all default 'data' on fields, this data is stored by the form anyways.
            $children = new \AppendIterator();
            $children->append(new \IteratorIterator($formBuilder));
            foreach($children as $child) {
                /* @var $child \Symfony\Component\Form\FormBuilderInterface */
                $children->append(new \IteratorIterator($child));
                if(!$child instanceof ButtonBuilder)
                    $child->setData(null);
            }
        }
        $form = $buildFormEvent->getFormBuilder()->getForm();
        $eventDispatcher->dispatch(FormEvents::SETDATA, new SetDataEvent($formEntity, $form, $enrollment));
        return $form;
    }

    public function formSubmitEvent(SubmitFormEvent $event)
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
        $data = $event->getEnrollment()->getData();
        $children = new \AppendIterator();
        $children->append(new \IteratorIterator($event->getSubmittedForm()));
        foreach($children as $child) {
            /* @var $child \Symfony\Component\Form\Form */
            $children->append(new \IteratorIterator($child));
            if($child->getConfig()->getDisabled()) {
                $propertyPath = $child->getPropertyPath()->__toString();
                $parent = $child;
                while(($parent = $parent->getParent())&&!$parent->isRoot()) {
                    $propertyPath = $parent->getPropertyPath().$propertyPath;
                }
                $propertyAccessor->setValue($data, $propertyPath, $child->getData());
            }
        }
        $event->getEnrollment()->setData($data);
    }

    public function uiFormEvent(SubmittedFormTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $form = $this->createForm($eventDispatcher, $this->formFactory->createBuilder(), $event->getForm());
        $event->addTemplate(new TemplateReference('AppBundle', 'Enrollment', 'form', 'html', 'twig'), ['form'=>$form]);
        $event->setSubmittedForm($form);
    }

    public function uiSuccessEvent(EnrollmentTemplateEvent $event, $eventName, EventDispatcherInterface $eventDispatcher)
    {
        $form = $this->createForm($eventDispatcher, $this->formFactory->createBuilder()->setDisabled($event->isEditDisabled()), $event->getForm(), $event->getEnrollment());
        if(!$event->getTemplates()->count())
            $event->addTemplate(new TemplateReference('AppBundle', 'Enrollment', 'success', 'html', 'twig'), ['form' => $form]);
        $event->setSubmittedForm($form);
    }
}
