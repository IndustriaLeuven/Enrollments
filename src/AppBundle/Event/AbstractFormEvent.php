<?php

namespace AppBundle\Event;

use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;

abstract class AbstractFormEvent extends Event
{
    /**
     * @var Form
     */
    private $form;

    /**
     * FormTemplateEvent constructor.
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
}