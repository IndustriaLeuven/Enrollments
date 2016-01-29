<?php

namespace AppBundle\Event\UI;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Templating\TemplateReferenceInterface;

class SuccessTemplateEvent extends Event
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var Enrollment
     */
    private $enrollment;

    private $templates;

    /**
     * FormTemplateEvent constructor.
     * @param Form $form
     */
    public function __construct(Form $form, Enrollment $enrollment)
    {
        $this->form = $form;
        $this->enrollment = $enrollment;
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
     * @return Enrollment
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }
}
