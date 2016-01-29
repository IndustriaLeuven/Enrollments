<?php

namespace AppBundle\Event;

final class FormEvents {
    /**
     * Emits {@link Form\BuildFormEvent}
     */
    const BUILD = 'app.form.build';
    /**
     * Emits {@link Form\SubmitFormEvent}
     */
    const SUBMIT = 'app.form.submit';
    /**
     * Emits {@link Form\SetDataEvent}
     */
    const SETDATA = 'app.form.set_data';
}
