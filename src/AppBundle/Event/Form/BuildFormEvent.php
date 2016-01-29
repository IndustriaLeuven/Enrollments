<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\FormBuilderInterface;

class BuildFormEvent extends Event
{
    /**
     * @var Form
     */
    private $form;

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
        $this->form = $form;
        $this->formBuilder = $formBuilder;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
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
