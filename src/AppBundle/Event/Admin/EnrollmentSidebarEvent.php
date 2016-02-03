<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use AppBundle\Event\UI\FormTemplateEvent;

class EnrollmentSidebarEvent extends FormTemplateEvent
{
    /**
     * @var Enrollment|null
     */
    private $enrollment;

    public function __construct(Form $form, Enrollment $enrollment = null)
    {
        parent::__construct($form);
        $this->enrollment = $enrollment;
    }

    /**
     * @return Enrollment|null
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }

    /**
     * @return bool
     */
    public function hasEnrollment()
    {
        return $this->enrollment !== null;
    }
}
