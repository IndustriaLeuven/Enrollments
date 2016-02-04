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
     * Cached flattened version of {@link $data}
     * @internal
     * @var array
     */
    private $_data_flattened;

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
        $this->_data_flattened = null;
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
     * Get flattened data
     * @return array
     */
    public function getFlattenedData()
    {
        if($this->_data_flattened === null) {
            // Algorithm based on http://stackoverflow.com/a/7858737
            $stack = $this->getData();
            $this->_data_flattened = [];
            while ($stack) {
                list($key, $value) = each($stack);
                unset($stack[$key]);
                if (is_array($value)) {
                    $build = [];
                    foreach ($value as $subKey => $node)
                        $build[$key . '.' . $subKey] = $node;
                    $stack = $build + $stack;
                    continue;
                }
                $this->_data_flattened[$key] = $value;

            }
        }
        return $this->_data_flattened;

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
