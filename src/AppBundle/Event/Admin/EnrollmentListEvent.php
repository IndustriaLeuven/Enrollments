<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use AppBundle\Plugin\Facet\FacetDefinition;
use AppBundle\Plugin\Facet\FacetDefinitionInterface;
use AppBundle\Plugin\Facet\FacetOption;
use AppBundle\Plugin\Table\TableColumnDefinitionInterface;
use AppBundle\Plugin\Table\TwigTableColumnDefinition;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\ParameterBag;

class EnrollmentListEvent extends AbstractFormEvent
{
    const ALL_TYPES = true;
    private static $all_types = [
        'html',
        'csv',
    ];

    /**
     * The query string of the request
     * @var ParameterBag
     */
    private $queryString;

    /**
     * @var TableColumnDefinitionInterface[][]
     */
    private $fields = [];

    /**
     * @var Criteria
     */
    private $criteria;

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var \Closure[]
     */
    private $filters = [];

    /**
     * @var FacetDefinitionInterface[]
     */
    private $facets = [];

    public function __construct(Form $form, ParameterBag $queryString, \Twig_Environment $twig)
    {
        parent::__construct($form);
        $this->queryString = $queryString;
        $this->criteria = new Criteria();
        $this->twig = $twig;
    }

    /**
     * @return ParameterBag
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param array $documentTypes
     * @param string $name
     * @param string $friendlyName
     * @param TemplateReference $templateReference
     * @param array $extraData
     * @return $this
     */
    public function setTemplatingField($documentTypes = self::ALL_TYPES, $name, $friendlyName, TemplateReference $templateReference, array $extraData = [])
    {
        $colDef = new TwigTableColumnDefinition($friendlyName, $templateReference, $this->twig, $extraData);
        $this->setField($documentTypes, $name, $colDef);
        return $this;
    }

    /**
     * @param array $documentTypes
     * @param $name
     * @param TableColumnDefinitionInterface $tableColumnDefinition
     * @return $this
     */
    public function setField($documentTypes = self::ALL_TYPES, $name, TableColumnDefinitionInterface $tableColumnDefinition)
    {
        if($documentTypes === self::ALL_TYPES) {
            $documentTypes = self::$all_types;
        }
        foreach($documentTypes as $docType) {
            $this->fields[$docType][$name] = $tableColumnDefinition;
        }
        return $this;
    }

    /**
     * @param array $documentTypes
     * @param string $name
     * @return $this
     */
    public function removeField($documentTypes = self::ALL_TYPES, $name)
    {
        if($documentTypes === self::ALL_TYPES) {
            $documentTypes = self::$all_types;
        }
        foreach($documentTypes as $docType) {
            unset($this->fields[$docType][$name]);
        }
        return $this;
    }

    /**
     * @param string $documentType
     * @param string $name
     * @return TableColumnDefinitionInterface|null
     */
    public function getField($documentType, $name)
    {
        if(isset($this->fields[$documentType]))
            if(isset($this->fields[$documentType][$name]))
                return $this->fields[$documentType][$name];
        return null;
    }

    /**
     * @param string $documentType
     * @return TableColumnDefinitionInterface[]
     */
    public function getFields($documentType)
    {
        return $this->fields[$documentType];
    }

    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    public function setFacet($name, FacetDefinitionInterface $facetDef)
    {
        $this->facets[$name] = $facetDef;
        return $this;
    }

    public function setSimpleFacet($facetName, $icon, array $options)
    {
        $opts = [];
        foreach($options as $name => $queryStringParameters) {
            $opts[] = new FacetOption($name, $queryStringParameters);
        }
        return $this->setFacet($facetName, new FacetDefinition($facetName, $icon, $opts));
    }

    /**
     * @return FacetDefinitionInterface[]
     */
    public function getFacets()
    {
        return $this->facets;
    }

    public function addFilter(callable $filter)
    {
        $this->filters[] = $filter;
    }

    /**
     * @return \Closure
     */
    public function getFilter()
    {
        return \Closure::bind(function($element) {
            foreach($this->filters as $filter) {
                if(!$filter($element))
                    return false;
            }
            return true;
        }, $this);
    }

    public function hasFilters()
    {
        return count($this->filters) > 0;
    }
}
