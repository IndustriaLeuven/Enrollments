<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use Symfony\Component\Form\FormBuilderInterface;

class EnrollmentEditEvent extends AbstractFormEvent
{
    /**
     * @var Enrollment
     */
    private $enrollment;

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
        parent::__construct($form);
        $this->enrollment = $enrollment;
        $this->formBuilder = $formBuilder;
    }

    /**
     * @return Enrollment
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }

    /**
     * @return FormBuilderInterface
     */
    public function getFormBuilder()
    {
        return $this->formBuilder;
    }
}
