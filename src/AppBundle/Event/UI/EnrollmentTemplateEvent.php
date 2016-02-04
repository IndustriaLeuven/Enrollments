<?php

namespace AppBundle\Event\UI;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;

class EnrollmentTemplateEvent extends SubmittedFormTemplateEvent
{
    /**
     * @var Enrollment
     */
    private $enrollment;

    /**
     * @var boolean
     */
    private $disableEdit;

    /**
     * SubmittedFormTemplateEvent constructor.
     * @param Form $form
     * @param Enrollment $enrollment
     * @param bool $disableEdit
     */
    public function __construct(Form $form, Enrollment $enrollment, $disableEdit = true)
    {
        parent::__construct($form);
        $this->enrollment = $enrollment;
        $this->disableEdit = $disableEdit;
    }

    /**
     * @return Enrollment
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }

    /**
     * @return bool
     */
    public function isEditDisabled()
    {
        return $this->disableEdit;
    }
}
