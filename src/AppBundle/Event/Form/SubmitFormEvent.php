<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Enrollment;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;

class SubmitFormEvent extends AbstractFilledFormEvent
{
    const TYPE_CREATE = 0;
    const TYPE_EDIT = 1;

    /**
     * @var int
     */
    private $type;

    /**
     * SubmitFormEvent constructor.
     * @param \AppBundle\Entity\Form $form
     * @param Form $submittedForm
     * @param Enrollment $enrollment
     * @param int $type
     */
    public function __construct(\AppBundle\Entity\Form $form, Form $submittedForm, Enrollment $enrollment, $type = self::TYPE_CREATE)
    {
        parent::__construct($form, $submittedForm, $enrollment);
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Enrollment
     */
    public function getEnrollment()
    {
        return parent::getEnrollment();
    }
}
