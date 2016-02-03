<?php

namespace AppBundle\Event\UI;

use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Templating\TemplateReferenceInterface;

class SubmittedFormTemplateEvent extends FormTemplateEvent
{
    /**
     * @var \Symfony\Component\Form\Form
     */
    private $submittedForm;

    /**
     * @param \Symfony\Component\Form\Form $submittedForm
     * @return SubmittedFormTemplateEvent
     */
    public function setSubmittedForm($submittedForm)
    {
        $this->submittedForm = $submittedForm;
        return $this;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getSubmittedForm()
    {
        return $this->submittedForm;
    }
}
