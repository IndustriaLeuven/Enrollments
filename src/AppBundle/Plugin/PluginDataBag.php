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
     * @param PluginInterface $plugin
     * @return mixed
     */
    public function get(PluginInterface $plugin)
    {
        if(!$this->has($plugin))
            return null;
        return $this->parameters[$plugin->getName()];
    }

    /**
     * @param PluginInterface $plugin
     * @param mixed $data
     * @return $this
     */
    public function set(PluginInterface $plugin, $data)
    {
        $this->parameters[$plugin->getName()] = $data;
        return $this;
    }

    /**
     * @param PluginInterface $plugin
     * @return bool
     */
    public function has(PluginInterface $plugin)
    {
       return isset($this->parameters[$plugin->getName()]);
    }

    /**
     * @param PluginInterface $plugin
     * @return $this
     */
    public function remove(PluginInterface $plugin)
    {
        unset($this->parameters[$plugin->getName()]);
        return $this;
    }

}
