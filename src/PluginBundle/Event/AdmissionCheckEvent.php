<?php

namespace PluginBundle\Event;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Symfony\Component\EventDispatcher\Event;

class AdmissionCheckEvent extends Event
{
    /**
     * Emits {@link AdmissionCheckEvent}
     */
    const EVENT_NAME = 'plugin.admision_check.check';

    const VALIDITY_ABSTAIN = 0;
    const VALIDITY_GRANT = 1;
    const VALIDITY_DENY = -1;

    /**
     * @var Enrollment
     */
    private $enrollment;

    private $validity = self::VALIDITY_ABSTAIN;
    private $reasons;

    /**
     * AdmissionCheckEvent constructor.
     *
     * @param Enrollment $enrollment
     */
    public function __construct(Enrollment $enrollment = null)
    {
        $this->enrollment = $enrollment;
        $this->reasons = [];
    }

    /**
     * @param Enrollment $enrollment
     * @internal
     */
    public function setEnrollment(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
    }

    /**
     * @return Enrollment|null
     */
    public function getEnrollment()
    {
        return $this->enrollment;
    }

    /**
     * Adds a vote to the validity of the admission.
     *
     * Validity can be granted or denied.
     * As soon as one voter denies validity, validity is denied.
     * If there are no grant or deny votes, validity is denied.
     *
     * @param int $validity {@link self::VALIDITY_GRANT} or {@link self::VALIDITY_DENY}
     * @return $this
     */
    public function addValidityVote($validity)
    {
        if($validity === self::VALIDITY_DENY)
            $this->validity = self::VALIDITY_DENY;
        if($this->validity === self::VALIDITY_ABSTAIN)
            $this->validity = $validity;
        return $this;
    }

    /**
     * Votes with a reason
     *
     * @param int $validity {@link self::VALIDITY_GRANT} or {@link self::VALIDITY_DENY}
     * @param string $pluginName Name of the plugin that voted for this validity
     * @param string $reason Reason why the plugin voted this way
     * @return $this
     */
    public function addReasonedVote($validity, $pluginName, $reason)
    {
        return $this->addValidityVote($validity)->addReason($validity, $pluginName, $reason);
    }

    /**
     * Adds a reason why a plugin voted a certain way.
     *
     * Does not add a vote by itself.
     *
     * @see addReasonedVote()
     * @param int $validity {@link self::VALIDITY_GRANT}, {@link self::VALIDITY_ABSTAIN} or {@link self::VALIDITY_DENY}
     * @param string $pluginName Name of the plugin that voted for this validity
     * @param string $reason Reason why the plugin voted this way
     * @return $this
     */
    public function addReason($validity, $pluginName, $reason)
    {
        if(!isset($this->reasons[$validity]))
            $this->reasons[$validity] = [];
        if(!isset($this->reasons[$validity][$pluginName]))
            $this->reasons[$validity][$pluginName] = [];
        $this->reasons[$validity][$pluginName][] = $reason;
        return $this;
    }

    /**
     * Gets an array of reasons why the current validity was voted for
     * @return array
     */
    public function getReasons()
    {
        if(!isset($this->reasons[$this->validity]))
            return [];
        return call_user_func_array('array_merge', $this->reasons[$this->validity]);
    }

    public function isValid()
    {
        return $this->validity === self::VALIDITY_GRANT;
    }
}
