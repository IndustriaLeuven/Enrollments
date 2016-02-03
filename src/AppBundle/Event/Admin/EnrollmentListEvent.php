<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Form;
use AppBundle\Event\AbstractFormEvent;
use AppBundle\Plugin\TableColumnDefinition;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Schema\Table;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\ParameterBag;

class EnrollmentListEvent extends AbstractFormEvent
{
    /**
     * The query string of the request
     * @var ParameterBag
     */
    private $queryString;

    /**
     * @var TableColumnDefinition[]
     */
    private $fields = [];

    /**
     * @var Criteria
     */
    private $criteria;

    public function __construct(Form $form, ParameterBag $queryString)
    {
        parent::__construct($form);
        $this->queryString = $queryString;
        $this->criteria = new Criteria();
    }

    /**
     * @return ParameterBag
     */
    public function getQueryString()
    {
        return $this->queryString;
    }

    /**
     * @param string $name
     * @param string $friendlyName
     * @param TemplateReference $templateReference
     * @param array $extraData
     * @return TableColumnDefinition
     */
    public function setField($name, $friendlyName, TemplateReference $templateReference, array $extraData = [])
    {
        $this->fields[$name] = new TableColumnDefinition($friendlyName, $templateReference, $extraData);
    }

    /**
     * @param $name
     * @return TableColumnDefinition|null
     */
    public function getField($name)
    {
        if(isset($this->fields[$name]))
            return $this->fields[$name];
        return null;
    }

    /**
     * @return TableColumnDefinition[]
     */
    public function getFields()
    {
        return array_values($this->fields);
    }

    /**
     * @return Criteria
     */
    public function getCriteria()
    {
        return $this->criteria;
    }
}
