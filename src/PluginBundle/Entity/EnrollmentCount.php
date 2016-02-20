<?php

namespace PluginBundle\Entity;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Doctrine\ORM\Mapping as ORM;

/**
 * EnrollmentCount
 *
 * @ORM\Table(name="plugin_enrollment_count")
 * @ORM\Entity(repositoryClass="PluginBundle\Entity\EnrollmentCountRepository")
 */
class EnrollmentCount
{
    /**
     * @var Form
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Form")
     */
    private $form;

    /**
     * @var Enrollment
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Enrollment")
     * @ORM\Id()
     */
    private $enrollment;

    /**
     * @var integer
     *
     * @ORM\Column(name="count", type="integer")
     */
    private $count;

    /**
     * EnrollmentCount constructor.
     * @param Enrollment $enrollment
     */
    public function __construct(Enrollment $enrollment)
    {
        $this->enrollment = $enrollment;
        $this->form = $enrollment->getForm();
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
     * Set count
     *
     * @param integer $count
     *
     * @return EnrollmentCount
     */
    public function setCount($count)
    {
        $this->count = $count;

        return $this;
    }

    /**
     * Get count
     *
     * @return integer
     */
    public function getCount()
    {
        return $this->count;
    }
}

