<?php

namespace AppBundle\Event\Plugin;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;

class PluginSubmitFormEvent extends Event
{
    /**
     * @var Form
     */
    private $submittedForm;

    /**
     * @var \AppBundle\Entity\Form
     */
    private $form;

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
        $this->submittedForm = $submittedForm;
        $this->form = $form;
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
     * @return \AppBundle\Entity\Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return int {@link self::TYPE_NEW}, {@link self::TYPE_EDIT} or {@link self::TYPE_DELETE}
     */
    public function getType()
    {
        return $this->type;
    }

}
