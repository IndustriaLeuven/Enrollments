<?php

namespace AppBundle\Event\UI;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Templating\TemplateReferenceInterface;

class EnrollmentTemplateEvent extends FormTemplateEvent
{
    /**
     * @var Enrollment
     */
    private $enrollment;

    /**
     * SubmittedFormTemplateEvent constructor.
     * @param Form $form
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
