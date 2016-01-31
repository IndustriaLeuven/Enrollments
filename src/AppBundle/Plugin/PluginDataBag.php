<?php

namespace AppBundle\Plugin;

class PluginDataBag
{
    private $parameters;

    /**
     * PluginDataBag constructor.
     * @param array $parameters
     */
    public function __construct(array &$parameters = [])
    {
        $this->parameters = &$parameters;
    }


    /**
     * @param $plugin
     * @return mixed
     */
    public function get($plugin)
    {
        if(!$this->has($plugin))
            return null;
        return $this->parameters[$plugin];
    }

    /**
     * @param $plugin
     * @param mixed $data
     * @return $this
     */
    public function set($plugin, $data)
    {
        $this->parameters[$plugin] = $data;
        return $this;
    }

    /**
     * @param $plugin
     * @return bool
     */
    public function has($plugin)
    {
       return isset($this->parameters[$plugin]);
    }

    /**
     * @param $plugin
     * @return $this
     */
    public function remove($plugin)
    {
        unset($this->parameters[$plugin]);
        return $this;
    }

}
