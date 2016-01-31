<?php

namespace AppBundle\Event;

final class PluginEvents
{
    /**
     * Emits {@link Plugin\PluginBuildFormEvent}
     */
    const BUILD_FORM = 'app.plugin.form.build';
    /**
     * Emits {@link Plugin\PluginSubmitFormEvent}
     */
    const SUBMIT_FORM = 'app.plugin.form.submit';
}