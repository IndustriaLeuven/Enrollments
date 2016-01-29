<?php

namespace AppBundle\Event;

final class PluginEvents
{
    /**
     * Emits {@link Plugin\GetPluginEvent}
     */
    const GET = 'app.plugin.get';
    /**
     * Emits {@link Plugin\BuildFormEvent}
     */
    const BUILD_FORM = 'app.plugin.form.build';
    /**
     * Emits {@link Plugin\SubmitFormEvent}
     */
    const SUBMIT_FORM = 'app.plugin.form.submit';
}