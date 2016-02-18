<?php

namespace AppBundle\Event\Admin;

use AppBundle\Entity\Enrollment;
use AppBundle\Event\AbstractFormEvent;

class EnrollmentBatchEvent extends AbstractFormEvent
{
    /**
     * @var array
     */
    private $actions = [];

    /**
     * @param string $name
     * @param string $label
     * @param callable $callback Takes an {@link \AppBundle\Entity\Enrollment} as parameter
     * @return $this
     */
    public function setAction($name, $label, callable $callback)
    {
        $this->actions[$name] = [$label, $callback];
        return $this;
    }

    /**
     * @param string $name
     * @param Enrollment[] $enrollments
     */
    public function handleAction($name, array $enrollments)
    {
        foreach($enrollments as $enrollment)
            $this->actions[$name][1]($enrollment);
    }

    /**
     * @return array
     */
    public function getChoices()
    {
        return array_flip(array_map(function($elem) {
            return $elem[0];
        }, $this->actions));
    }
}
