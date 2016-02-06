<?php

namespace AppBundle\Plugin\Table;

use Symfony\Component\Templating\TemplateReferenceInterface;

class TwigTableColumnDefinition implements TableColumnDefinitionInterface
{
    /**
     * @var string
     */
    private $columnHeader;

    /**
     * @var \Twig_TemplateInterface
     */
    private $template;

    /**
     * @var array
     */
    private $extraData;

    /**
     * TwigTableColumnDefinition constructor.
     * @param string $columnHeader
     * @param TemplateReferenceInterface $template
     * @param \Twig_Environment $twig
     * @param array $extraData
     */
    public function __construct($columnHeader, TemplateReferenceInterface $template, \Twig_Environment $twig, array $extraData = [])
    {
        $this->columnHeader = $columnHeader;
        $this->template = $twig->loadTemplate($template);
        $this->extraData = $extraData;
    }


    public function getColumnHeader()
    {
        return $this->columnHeader;
    }

    public function renderColumnData(array $data)
    {
        return trim($this->template->render($data + $this->extraData));
    }
}
