<?php

namespace AppBundle\Plugin;

use Symfony\Component\Templating\TemplateReferenceInterface;

class TableColumnDefinition
{
    /**
     * @var string
     */
    private $friendlyName;

    /**
     * @var TemplateReferenceInterface
     */
    private $template;

    /**
     * @var array
     */
    private $extraData = [];

    /**
     * TableColumnDefinition constructor.
     * @param $friendlyName
     * @param TemplateReferenceInterface $template
     * @param array $extraData
     */
    public function __construct($friendlyName, TemplateReferenceInterface $template, array $extraData = [])
    {
        $this->friendlyName = $friendlyName?:$name;
        $this->template = $template;
        $this->extraData = $extraData;
    }

    /**
     * @return string
     */
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }

    /**
     * @return TemplateReferenceInterface
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return array
     */
    public function getExtraData()
    {
        return $this->extraData;
    }
}
