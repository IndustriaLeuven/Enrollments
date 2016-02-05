<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use Symfony\Component\Form\FormBuilderInterface;

class EnrollmentEditSubmitEvent extends EnrollmentEvent
{
    /**
     * @var \Symfony\Component\Form\Form
     */
    private $submittedForm;

    /**
     * PluginBuildFormEvent constructor.
     * @param Form $form
     * @param Enrollment $enrollment
     * @param \Symfony\Component\Form\Form $submittedForm
     */
    public function __construct(Form $form, Enrollment $enrollment, \Symfony\Component\Form\Form $submittedForm)
    {
        parent::__construct($form, $enrollment);
        $this->submittedForm = $submittedForm;

    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getSubmittedForm()
    {
        return $this->submittedForm;
    }
}
