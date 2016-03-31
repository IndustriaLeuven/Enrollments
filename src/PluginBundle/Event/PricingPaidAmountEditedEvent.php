<?php

namespace PluginBundle\Event;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;

class PricingPaidAmountEditedEvent extends Event
{
    const EVENT_NAME = 'plugin.pricing.paid_amount_edited';
    /**
     * @var Form
     */
    private $form;

    /**
     * @var Enrollment
     */
    private $enrollment;

    /**
     * @var float
     */
    private $previousPaidAmount;

    /**
     * @var float
     */
    private $previousTotalAmount;

    /**
     * PricingPaidAmountEditedEvent constructor.
     * @param Form $form
     * @param Enrollment $enrollment
     * @param float $previousPaidAmount
     * @param float $previousTotalAmount
     */
    public function __construct(Form $form, Enrollment $enrollment, $previousPaidAmount, $previousTotalAmount)
    {
        $this->enrollment = $enrollment;
        $this->previousPaidAmount = $previousPaidAmount;
        $this->previousTotalAmount = $previousTotalAmount;
        $this->form = $form;
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
     * @return float
     */
    public function getPreviousPaidAmount()
    {
        return $this->previousPaidAmount;
    }

    /**
     * @return float
     */
    public function getPreviousTotalAmount()
    {
        return $this->previousTotalAmount;
    }
}
