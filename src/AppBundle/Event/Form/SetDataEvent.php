<?php

namespace AppBundle\Event\Form;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;

class SetDataEvent extends Event
{
    /**
     * @var Form
     */
    private $form;

    /**
     * @var Enrollment|null
     */
    private $enrollment;

    /**
     * @var \Symfony\Component\Form\Form
     */
    private $userForm;

    /**
     * SetDataEvent constructor.
     * @param Form $form
     * @param \Symfony\Component\Form\Form $userForm
     * @param Enrollment|null $enrollment
     */
    public function __construct(Form $form, \Symfony\Component\Form\Form $userForm, Enrollment $enrollment = null)
    {
        $this->form = $form;
        $this->enrollment = $enrollment;
        $this->userForm = $userForm;
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }

    /**
     * @return Enrollment|null
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }

    /**
     * @return bool
     */
    public function hasEnrollment()
    {
        return $this->enrollment !== null;
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    public function getUserForm()
    {
        return $this->userForm;
    }
}