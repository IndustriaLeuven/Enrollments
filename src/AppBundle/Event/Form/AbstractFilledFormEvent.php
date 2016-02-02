<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Enrollment;
use AppBundle\Event\AbstractFormEvent;
use Symfony\Component\Form\Form;

abstract class AbstractFilledFormEvent extends AbstractFormEvent
{
    /**
     * @var Form
     */
    private $submittedForm;

    /**
     * @var Enrollment|null
     */
    private $enrollment;

    public function __construct(\AppBundle\Entity\Form $form, Form $submittedForm, Enrollment $enrollment = null)
    {
        parent::__construct($form);
        $this->submittedForm = $submittedForm;
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
     * @return Enrollment|null
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }
}
