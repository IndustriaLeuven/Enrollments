<?php

namespace AppBundle\Plugin\Table;

interface TableColumnDefinitionInterface
{
    public function getColumnHeader();
    public function renderColumnData(array $data);
}
