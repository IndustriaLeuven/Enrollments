<?php

namespace AppBundle\Plugin\Facet;

use Symfony\Component\HttpFoundation\ParameterBag;

class FacetOption implements FacetOptionInterface
{
    /**
     * @var string|null
     */
    private $icon;

    /**
     * @var string
     */
    private $text;

    /**
     * @var array
     */
    private $queryParams;

    /**
     * FacetOption constructor.
     * @param null|string $icon
     * @param string $text
     * @param array $queryParams
     */
    public function __construct($text, array $queryParams, $icon = null )
    {
        $this->text = $text;
        $this->queryParams = $queryParams;
        $this->icon = $icon;
    }


    /**
     * @return null|string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @return array
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function isActive(ParameterBag $queryString)
    {
        return self::validateDeepContains($this->queryParams, $queryString->all());
    }

    public function isClearing()
    {
        return self::isNulling($this->queryParams);
    }

    private static function validateDeepContains($queryParam, $get)
    {
        if(is_array($queryParam)&&is_array($get)) {
            foreach($queryParam as $key => $value) {
                if(isset($get[$key])) {
                    if(self::isNulling($value))
                        return false;
                    elseif(!self::validateDeepContains($value, $get[$key]))
                        return false;
                } else {
                    if(!self::isNulling($value))
                        return false;
                }
            }
        } else {
            return $queryParam === $get;
        }

        return true;
    }

    private static function isNulling($queryParam)
    {
        if(is_array($queryParam)) {
            foreach ($queryParam as $value)
                if (!self::isNulling($value))
                    return false;
            return true;
        } else {
            return $queryParam === null;
        }
    }
}

