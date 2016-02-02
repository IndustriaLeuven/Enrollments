<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;

class SetDataEvent extends AbstractFilledFormEvent
{
    /**
     * @return bool
     */
    public function hasEnrollment()
    {
        return $this->getEnrollment() !== null;
    }

    /**
     * @deprecated
     * @return \Symfony\Component\Form\Form
     */
    public function getUserForm()
    {
        @trigger_error(__METHOD__.'() is deprecated, use '.__CLASS__.'::getSubmittedForm() instead.', E_USER_DEPRECATED);
        return $this->getSubmittedForm();
    }
}
