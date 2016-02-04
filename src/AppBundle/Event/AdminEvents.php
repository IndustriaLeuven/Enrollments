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

    /**
     * Emits {@link UI\EnrollmentTemplateEvent}
     */
    const ENROLLMENT_GET = 'app.admin.enrollment.get';

    /**
     * Emits {@link Admin\EnrollmentEditEvent}
     */
    const ENROLLMENT_EDIT = 'app.admin.enrollment.edit';

    /**
     * Emits {@link Admin\EnrollmentEditSubmitEvent}
     */
    const ENROLLMENT_EDIT_SUBMIT = 'app.admin.enrollment.edit_submit';
}
