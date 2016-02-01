<?php

namespace AppBundle\Form;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ArrayKeyChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class FinderChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var Finder
     */
    private $finder;

    /**
     * @var string
     */
    private $extension;

    /**
     * @var array
     */
    private $choices;

    /**
     * FinderChoiceLoader constructor.
     * @param Finder $finder
     */
    public function __construct(Finder $finder, $extension)
    {
        $this->finder = $finder;
        $this->extension = $extension;
    }

    /**
     * Loads a list of choices.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param null|callable $value The callable which generates the values
     *                             from choices
     *
     * @return ChoiceListInterface The loaded choice list
     */
    public function loadChoiceList($value = null)
    {
        if(!$this->choices) {
            $this->choices = array();
            foreach ($this->finder as $file) {
                /* @var $file SplFileInfo */
                $this->choices[$file->getRelativePathname()] = $file->getRelativePathname();
            }
        }
        return new ArrayChoiceList($this->choices, $value);
    }

    /**
     * Loads the choices corresponding to the given values.
     *
     * The choices are returned with the same keys and in the same order as the
     * corresponding values in the given array.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param string[] $values An array of choice values. Non-existing
     *                             values in this array are ignored
     * @param null|callable $value The callable generating the choice values
     *
     * @return array An array of choices
     */
    public function loadChoicesForValues(array $values, $value = null)
    {
        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    /**
     * Loads the values corresponding to the given choices.
     *
     * The values are returned with the same keys and in the same order as the
     * corresponding choices in the given array.
     *
     * Optionally, a callable can be passed for generating the choice values.
     * The callable receives the choice as first and the array key as the second
     * argument.
     *
     * @param array $choices An array of choices. Non-existing choices in
     *                             this array are ignored
     * @param null|callable $value The callable generating the choice values
     *
     * @return string[] An array of choice values
     */
    public function loadValuesForChoices(array $choices, $value = null)
    {
        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }

}
