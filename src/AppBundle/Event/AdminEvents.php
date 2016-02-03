<?php

namespace AppBundle\Event;

final class AdminEvents
{
    /**
     * Emits {@link UI\SubmittedFormTemplateEvent}
     */
    const FORM_GET = 'app.admin.form.get';

    /**
     * Emits {@link Admin\EnrollmentSidebarEvent}
     */
    const ENROLLMENT_SIDEBAR = 'app.admin.enrollment.sidebar';

    /**
     * Emits {@link Admin\EnrollmentListEvent}
     */
    const ENROLLMENT_LIST = 'app.admin.enrollment.list';

}
