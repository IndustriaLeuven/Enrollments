<?php

namespace AppBundle\Event;

final class UIEvents
{
    /**
     * Emits {@link UI\FormTemplateEvent}
     */
    const FORM = 'app.ui.form';

    /**
     * Emits {@link UI\SuccessTemplateEvent}
     */
    const SUCCESS = 'app.ui.success';
}
