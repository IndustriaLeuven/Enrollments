<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Enrollment;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;

class SubmitFormEvent extends AbstractFilledFormEvent
{
    /**
     * SubmitFormEvent constructor.
     * @param \AppBundle\Entity\Form $form
     * @param Form $submittedForm
     * @param Enrollment $enrollment
     */
    public function __construct(\AppBundle\Entity\Form $form, Form $submittedForm, Enrollment $enrollment)
    {
        parent::__construct($form, $submittedForm, $enrollment);
    }

    /**
     * @return Enrollment
     */
    public function getEnrollment()
    {
        return parent::getEnrollment();
    }
}
