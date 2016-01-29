<?php

namespace AppBundle\Entity;

use AppBundle\Plugin\PluginDataBag;
use Doctrine\ORM\Mapping as ORM;

/**
 * Enrollment
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Enrollment
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
     * @var array
     *
     * @ORM\Column(name="data", type="json_array")
     */
    private $data;

    /**
     * @var PluginDataBag
     *
     * @ORM\Column(name="plugin_data", type="array")
     */
    private $pluginData = [];

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp", type="datetimetz")
     */
    private $timestamp;

    /**
     * @var Form
     *
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="enrollments")
     */
    private $form;

    /**
     * Enrollment constructor.
     * @param Form $form
     */
    public function __construct(Form $form)
    {
        $this->form = $form;
        $this->timestamp = new \DateTime();
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
     * Set data
     *
     * @param array $data
     *
     * @return Enrollment
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return PluginDataBag
     */
    public function getPluginData()
    {
        return new PluginDataBag($this->pluginData);
    }

    /**
     * Get timestamp
     *
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Get form
     *
     * @return Form
     */
    public function getForm()
    {
        return $this->form;
    }
}
