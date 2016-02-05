<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;

class EnrollmentEvent extends AbstractFormEvent
{
    /**
     * @var Enrollment
     */
    private $enrollment;

    /**
     * EnrollmentEvent constructor.
     * @param Form $form
     * @param Enrollment $enrollment
     */
    public function __construct(Form $form, Enrollment $enrollment)
    {
        parent::__construct($form);
        $this->enrollment = $enrollment;
    }

    /**
     * @return Enrollment
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }

}
