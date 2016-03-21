<?php

namespace AppBundle\Entity;

use AppBundle\Plugin\PluginDataBag;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Form
 *
 * @ORM\Table()
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Form
{
    use SoftDeleteableEntity;
    use TimestampableEntity;
    use BlameableEntity;

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
     * @var array
     *
     * @ORM\Column(name="auth_edit_form", type="simple_array")
     */
    private $editFormGroups = [];

    /**
     * @var array
     *
     * @ORM\Column(name="auth_list_enrollments", type="simple_array")
     */
    private $listEnrollmentsGroups = [];

    /**
     * @var array
     *
     * @ORM\Column(name="auth_edit_enrollments", type="simple_array")
     */
    private $editEnrollmentsGroups = [];

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
     * @return array
     */
    public function getEditFormGroups()
    {
        return $this->editFormGroups;
    }

    /**
     * @param array $editFormGroups
     * @return Form
     */
    public function setEditFormGroups($editFormGroups)
    {
        $this->editFormGroups = $editFormGroups;
        return $this;
    }

    /**
     * @return array
     */
    public function getListEnrollmentsGroups()
    {
        return $this->listEnrollmentsGroups;
    }

    /**
     * @param array $listEnrollmentsGroups
     * @return Form
     */
    public function setListEnrollmentsGroups($listEnrollmentsGroups)
    {
        $this->listEnrollmentsGroups = $listEnrollmentsGroups;
        return $this;
    }

    /**
     * @return array
     */
    public function getEditEnrollmentsGroups()
    {
        return $this->editEnrollmentsGroups;
    }

    /**
     * @param array $editEnrollmentsGroups
     * @return Form
     */
    public function setEditEnrollmentsGroups($editEnrollmentsGroups)
    {
        $this->editEnrollmentsGroups = $editEnrollmentsGroups;
        return $this;
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
     * @return \Doctrine\Common\Collections\Collection|Selectable
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
