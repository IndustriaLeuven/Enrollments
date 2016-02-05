<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use Symfony\Component\Form\FormBuilderInterface;

class EnrollmentEditEvent extends EnrollmentEvent
{
    /**
     * @var FormBuilderInterface
     */
    private $formBuilder;

    /**
     * PluginBuildFormEvent constructor.
     * @param Form $form
     * @param Enrollment $enrollment
     * @param FormBuilderInterface $formBuilder
     */
    public function __construct(Form $form, Enrollment $enrollment, FormBuilderInterface $formBuilder)
    {
        parent::__construct($form, $enrollment);
        $this->formBuilder = $formBuilder;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }
}
