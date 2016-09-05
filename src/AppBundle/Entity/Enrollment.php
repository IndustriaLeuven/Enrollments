<?php

namespace AppBundle\Entity;

use AppBundle\Plugin\PluginDataBag;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * Enrollment
 *
 * @ORM\Table()
 * @ORM\Entity
 * @Gedmo\SoftDeleteable(fieldName="deletedAt")
 */
class Enrollment
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
    }

    /**
     * Get id
     *
     * @return string
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
     * @deprecated Use {@link getCreatedAt()} instead
     * @return \DateTime
     */
    public function getTimestamp()
    {
        @trigger_error(__METHOD__.' is deprecated. Use '.__CLASS__.'::getCreatedAt() instead.', E_USER_DEPRECATED);
        return $this->getCreatedAt();
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

    /**
     * @param Form $form
     * @return Enrollment
     */
    public function setForm($form)
    {
        $this->form = $form;
        return $this;
    }
}
