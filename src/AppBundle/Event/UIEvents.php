<?php

namespace AppBundle\Event;

final class UIEvents
{
    /**
     * Emits {@link UI\SubmittedFormTemplateEvent}
     */
    const FORM = 'app.ui.form';

    /**
     * Emits {@link UI\EnrollmentTemplateEvent}
     */
    const SUCCESS = 'app.ui.success';
}
