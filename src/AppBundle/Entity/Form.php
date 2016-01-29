<?php

namespace AppBundle\Entity;

use AppBundle\Plugin\PluginDataBag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Form
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Form
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var Enrollment[]
     *
     * @ORM\OneToMany(targetEntity="Enrollment", mappedBy="form")
     */
    private $enrollments;

    /**
     * @var array
     *
     * @ORM\Column(name="plugin_data", type="array")
     */
    private $pluginData = [];

    /**
     * Form constructor.
     */
    public function __construct()
    {
        $this->enrollments = new ArrayCollection();
        $this->pluginData = [];
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Form
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add enrollment
     *
     * @param Enrollment $enrollment
     *
     * @return Form
     */
    public function addEnrollment(Enrollment $enrollment)
    {
        $this->enrollments[] = $enrollment;

        return $this;
    }

    /**
     * Remove enrollment
     *
     * @param Enrollment $enrollment
     */
    public function removeEnrollment(Enrollment $enrollment)
    {
        $this->enrollments->removeElement($enrollment);
    }

    /**
     * Get enrollments
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEnrollments()
    {
        return $this->enrollments;
    }

    /**
     * @return PluginDataBag
     */
    public function getPluginData()
    {
        return new PluginDataBag($this->pluginData);
    }
}
