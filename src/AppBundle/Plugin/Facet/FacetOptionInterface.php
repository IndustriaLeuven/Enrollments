<?php

namespace AppBundle\Plugin\Facet;

use Symfony\Component\HttpFoundation\ParameterBag;

interface FacetOptionInterface
{
    /**
     * @return string|null
     */
    public function getIcon();

    /**
     * @return string
     */
    public function getText();

    /**
     * @return array
     */
    public function getQueryParams();

    /**
     * @param ParameterBag $queryString
     * @return bool
     */
    public function isActive(ParameterBag $queryString);

    /**
     * @return bool
     */
    public function isClearing();
}
