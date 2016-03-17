<?php

namespace PluginBundle\Entity;

use AppBundle\Entity\Enrollment;
use AppBundle\Entity\Form;
use Doctrine\ORM\Mapping as ORM;

/**
 * UniqueFieldData
 *
 * @ORM\Table(name="plugin_unique_field")
 * @ORM\Entity(repositoryClass="PluginBundle\Entity\UniqueFieldDataRepository")
 */
class UniqueFieldData
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     */
    private $enrollment;

    /**
     * @var string
     *
     * @ORM\Column(name="fieldName", type="string")
     */
    private $fieldName;

    /**
     * @var object
     *
     * @ORM\Column(name="data", type="object")
     */
    private $data;


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
     * @param Enrollment $enrollment
     * @return UniqueFieldData
     */
    public function setEnrollment($enrollment)
    {
        $this->enrollment = $enrollment;
        $this->form = $enrollment->getForm();
        return $this;
    }

    /**
     * Set fieldName
     *
     * @param string $fieldName
     *
     * @return UniqueFieldData
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;

        return $this;
    }

    /**
     * Get fieldName
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * Set data
     *
     * @param object $data
     *
     * @return UniqueFieldData
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return object
     */
    public function getData()
    {
        return $this->data;
    }
}

