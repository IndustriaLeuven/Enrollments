<?php

namespace AppBundle\Event\UI;

use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Templating\TemplateReferenceInterface;

class FormTemplateEvent extends Event
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var \Symfony\Component\Form\Form
     */
    private $submittedForm;

    private $templates;

    /**
     * FormTemplateEvent constructor.
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
        $this->templates = new \SplObjectStorage();
    }

    /**
     * @param TemplateReferenceInterface $templateReference
     * @param array $extraData
     */
    public function addTemplate(TemplateReferenceInterface $templateReference, array $extraData = [])
    {
        $this->templates->attach($templateReference, $extraData);
    }

    /**
     * @internal
     * @return \SplObjectStorage
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @param \Symfony\Component\Form\Form $submittedForm
     * @return FormTemplateEvent
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
