<?php

namespace AppBundle\Form;

use GuzzleHttp\ClientInterface;
use Symfony\Component\Form\ChoiceList\ArrayChoiceList;
use Symfony\Component\Form\ChoiceList\ChoiceListInterface;
use Symfony\Component\Form\ChoiceList\Loader\ChoiceLoaderInterface;

class AuthserverGroupsChoiceLoader implements ChoiceLoaderInterface
{
    /**
     * @var ChoiceListInterface
     */
    private $cachedChoiceList;

    /**
     * @var ClientInterface
     */
    private $guzzle;

    /**
     * @var array
     */
    private $properties;

    /**
     * AuthserverGroupsChoiceLoader constructor.
     * @param ClientInterface $guzzle
     * @param array $properties
     */
    public function __construct(ClientInterface $guzzle, array $properties)
    {
        $this->guzzle = $guzzle;
        $this->properties = $properties;
    }

    public function loadChoiceList($value = null)
    {
        if(!$this->cachedChoiceList) {
            $groups = [];
            $pageData = ['_links'=>['next'=>['href'=>'admin/groups.json?'.http_build_query(['q'=>$this->properties])]]];

            while(isset($pageData['_links'])&&isset($pageData['_links']['next'])&&isset($pageData['_links']['next']['href'])) {
                $pageData = json_decode($this->guzzle->request('GET', $pageData['_links']['next']['href'])
                    ->getBody(), true);

                foreach($pageData['items'] as $group) {
                    $groups[$group['display_name']] = $group['name'];
                }
            }
            $this->cachedChoiceList = new ArrayChoiceList($groups, $value);
        }

        return $this->cachedChoiceList;
    }

    public function loadChoicesForValues(array $values, $value = null)
    {
        return $this->loadChoiceList($value)->getChoicesForValues($values);
    }

    public function loadValuesForChoices(array $choices, $value = null)
    {
        return $this->loadChoiceList($value)->getValuesForChoices($choices);
    }
}
