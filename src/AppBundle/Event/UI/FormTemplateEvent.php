<?php

namespace AppBundle\Event\UI;

use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use Symfony\Component\Templating\TemplateReferenceInterface;

class FormTemplateEvent extends AbstractFormEvent
{
    /**
     * @var \SplObjectStorage
     */
    private $templates;

    public function __construct(Form $form)
    {
        parent::__construct($form);
        $this->templates = new \SplObjectStorage();
    }

    /**
     * @param TemplateReferenceInterface $templateReference
     * @param array $extraData
     * @return $this
     */
    public function addTemplate(TemplateReferenceInterface $templateReference, array $extraData = [])
    {
        $this->templates->attach($templateReference, $extraData);
        return $this;
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
     * @return $this
     */
    public function clearTemplates()
    {
        $this->templates = new \SplObjectStorage();
        return $this;
    }
}
