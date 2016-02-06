<?php

namespace AppBundle\Plugin\Table;

class CallbackTableColumnDefinition implements TableColumnDefinitionInterface
{
    /**
     * @var string
     */
    private $columnHeader;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var array
     */
    private $extraData;

    /**
     * TwigTableColumnDefinition constructor.
     * @param string $columnHeader
     * @param callable $callback
     * @param array $extraData
     */
    public function __construct($columnHeader, callable $callback, array $extraData = [])
    {
        $this->columnHeader = $columnHeader;
        $this->callback = $callback;
        $this->extraData = $extraData;
    }

    public function getColumnHeader()
    {
        return $this->columnHeader;
    }

    public function renderColumnData(array $data)
    {
        return htmlspecialchars(call_user_func($this->callback, $data + $this->extraData), ENT_QUOTES | ENT_SUBSTITUTE);
    }
}
