<?php

namespace AppBundle\Plugin\Facet;

use Symfony\Component\HttpFoundation\ParameterBag;

class FacetDefinition implements FacetDefinitionInterface
{
    /**
     * @var string
     */
    private $toolbarIcon;

    /**
     * @var string
     */
    private $toolbarText;

    /**
     * @var FacetOptionInterface[]
     */
    private $options;

    /**
     * FacetDefinition constructor.
     * @param string $toolbarIcon
     * @param string $toolbarText
     * @param FacetOptionInterface[] $options
     */
    public function __construct($toolbarText, $toolbarIcon, array $options)
    {
        $this->toolbarText = $toolbarText;
        $this->toolbarIcon = $toolbarIcon;
        $this->options = $options;
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return $this->toolbarIcon;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->toolbarText;
    }

    /**
     * @return FacetOptionInterface[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    public function isActive(ParameterBag $queryString)
    {
        foreach($this->options as $option) {
            if($option->isActive($queryString)&&!$option->isClearing())
                return true;
        }
        return false;
    }

}
