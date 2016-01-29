<?php

namespace AppBundle\Event\Plugin;

use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormBuilderInterface;

class BuildFormEvent extends Event
{
    /**
     * @var Form|null
     */
    private $form;

    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * BuildFormEvent constructor.
     * @param FormBuilderInterface $formBuilder
     */
    public function __construct(FormBuilderInterface $formBuilder, Form $form = null)
    {
        $this->formBuilder = $formBuilder;
        $this->form = $form;
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

    /**
     * @return Form|null
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return $this->form === null;
    }
}
