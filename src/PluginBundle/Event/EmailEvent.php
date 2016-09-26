<?php

namespace PluginBundle\Event;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;

class EmailEvent extends Event
{
    const ENROLL_EVENT = 'plugin.email.enroll';
    const PAID_EVENT = 'plugin.email.paid';
    const PAID_PARTIALLY_EVENT = 'plugin.email.paid_partially';

    /**
     * @var Form
     */
    private $form;

    /**
     * @var Enrollment
     */
    private $enrollment;

    /**
     * @var array
     */
    private $variables;

    /**
     * @var \Swift_Message
     */
    private $message;

    /**
     * PricingPaidAmountEditedEvent constructor.
     * @param Form $form
     * @param Enrollment $enrollment
     * @param array $variables
     */
    public function __construct(Form $form, Enrollment $enrollment, $variables)
    {
        $this->form = $form;
        $this->enrollment = $enrollment;
        $this->variables = $variables;
        $this->message = \Swift_Message::newInstance();
    }

    /**
     * @return \Swift_Message
     */
    public function getMessage()
    {
        return $this->message;
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

    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }

    /**
     * @param array $variables
     * @return EmailEvent
     */
    public function setVariables($variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @param string|int $name
     * @param mixed $value
     * @return EmailEvent
     */
    public function addVariable($name, $value)
    {
        $this->variables[$name] = $value;
        return $this;
    }
}
