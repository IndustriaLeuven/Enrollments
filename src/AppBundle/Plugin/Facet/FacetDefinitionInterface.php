<?php

namespace AppBundle\Plugin\Facet;

use Symfony\Component\HttpFoundation\ParameterBag;

interface FacetDefinitionInterface
{
    /**
     * @return string
     */
    public function getIcon();

    /**
     * @return string
     */
    public function getText();

    /**
     * @return FacetOptionInterface[]
     */
    public function getOptions();

    /**
     * @param ParameterBag $queryString
     * @return bool
     */
    public function isActive(ParameterBag $queryString);
}
