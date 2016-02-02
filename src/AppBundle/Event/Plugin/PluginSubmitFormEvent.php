<?php

namespace AppBundle\Event\Plugin;

use AppBundle\Event\AbstractFormEvent;
use Symfony\Component\Form\Form;

class PluginSubmitFormEvent extends AbstractFormEvent
{
    /**
     * @var Form
     */
    private $submittedForm;

    /**
     * @var int
     */
    private $type;

    const TYPE_NEW = 0;
    const TYPE_EDIT = 1;
    const TYPE_DELETE = 2;

    /**
     * PluginSubmitFormEvent constructor.
     * @param Form $submittedForm
     * @param \AppBundle\Entity\Form $form
     */
    public function __construct(Form $submittedForm, \AppBundle\Entity\Form $form, $type)
    {
        parent::__construct($form);
        $this->submittedForm = $submittedForm;
        $this->type = $type;
    }

    /**
     * @return Form
     */
    public function getSubmittedForm()
    {
        return $this->submittedForm;
    }

    /**
     * @return int {@link self::TYPE_NEW}, {@link self::TYPE_EDIT} or {@link self::TYPE_DELETE}
     */
    public function getType()
    {
        return $this->type;
    }
}
