<?php

namespace AppBundle\Event\Plugin;

use AppBundle\Plugin\PluginInterface;
use Symfony\Component\EventDispatcher\Event;

class GetPluginEvent extends Event
{
    private $plugins = [];

    public function registerPlugin(PluginInterface $plugin)
    {
        $this->plugins[$plugin->getName()] = $plugin;
    }

    /**
     * @return PluginInterface[]
     */
    public function getPlugins()
    {
        return $this->plugins;
    }
}
