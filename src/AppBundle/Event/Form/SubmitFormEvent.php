<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Enrollment;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;

class SubmitFormEvent extends Event
{
    /**
     * @var Form
     */
    private $submittedForm;

    /**
     * @var \AppBundle\Entity\Form
     */
    private $form;

    /**
     * @var Enrollment
     */
    private $enrollment;

    /**
     * SubmitFormEvent constructor.
     * @param Form $submittedForm
     * @param \AppBundle\Entity\Form $form
     * @param Enrollment $enrollment
     */
    public function __construct(Form $submittedForm, \AppBundle\Entity\Form $form, Enrollment $enrollment)
    {
        $this->submittedForm = $submittedForm;
        $this->form = $form;
        $this->enrollment = $enrollment;
    }

    /**
     * @return Form
     */
    public function getSubmittedForm()
    {
        return $this->submittedForm;
    }

    /**
     * @return \AppBundle\Entity\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return Enrollment
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }
}
