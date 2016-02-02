<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormBuilderInterface;

class BuildFormEvent extends AbstractFormEvent
{
    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * BuildFormEvent constructor.
     * @param Form $form
     * @param FormBuilderInterface $formBuilder
     */
    public function __construct(Form $form, FormBuilderInterface $formBuilder)
    {
        parent::__construct($form);
        $this->formBuilder = $formBuilder;
    }

    /**
     * @param FormBuilderInterface $formBuilder
     * @return $this
     */
    public function setFormBuilder(FormBuilderInterface $formBuilder)
    {
        $this->formBuilder = $formBuilder;
        return $this;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }
}
