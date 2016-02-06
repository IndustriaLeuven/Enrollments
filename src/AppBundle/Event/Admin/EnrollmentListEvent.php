<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use AppBundle\Plugin\Table\TableColumnDefinitionInterface;
use AppBundle\Plugin\Table\TwigTableColumnDefinition;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\ParameterBag;

class EnrollmentListEvent extends AbstractFormEvent
{
    const ALL_TYPES = null;
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
    public function setTemplatingField(array $documentTypes = self::ALL_TYPES, $name, $friendlyName, TemplateReference $templateReference, array $extraData = [])
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
    public function setField(array $documentTypes = self::ALL_TYPES, $name, TableColumnDefinitionInterface $tableColumnDefinition)
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
    public function removeField(array $documentTypes = self::ALL_TYPES, $name)
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
}
