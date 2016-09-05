# Plugins



## Execution order

For each event, enabled plugins are executed in a hard-coded order.

The events for which ordering is important are listed below together with the order in which the plugins are executed.

<!--
The numbers below are the priority of each event.
Because an ordered list item cannot start with a negative number, a 0 is used instead to indicate a negative priority.
Events are executed from highest priority to lowest, so keep the ordering in that way to make future extensions easier.
-->

### `UIEvents::FORM`

Event emitted when a fillable form has to be shown to the user.

 * Form entity (the one whose GUID was passed in the URL)
 * Submitted form (the symfony form that will be presented to the user; available after the AppBundle:DefaultEvents handler has been called)
 * List of templates to render

9001. Fakes 404 page (only when called the first time) **Stops propagation** [`PluginBundle:InternalForm`](internal_form.md)
257. Show message when before startDate or after endDate **Stops propagation** [`PluginBundle:Date`](date.md)
256. Show number of enrollments of the form [`PluginBundle:CountEnrollments`](count_enrollments.md)
255. Re-emits the `UIEvents::FORM` event with the form entity swapped out for the differentiated one, and copies the templates and form from this new event to the old event **Stops propagation** [`PluginBundle:RoleDifferentiation`](role_differentiation.md)
100. If the user is not logged in, show a warning about potentially changing forms [`PluginBundle:RoleDifferentiation`](role_differentiation.md)
5. If the user has already submitted a form, show message and javascript redirect them to their submission [`PluginBundle:UniqueUser`](unique_user.md)
0. Build and show form `AppBundle:DefaultEvents`
    * Emit `FormEvents::BUILD` with form entity from `UIEvents::FORM`
    * Emit `FormEvents::SETDATA` with the form entity from `UIEvents::FORM` and the form built from the formbuilder from `FormEvents::BUILD`
0253. Add payment information [`PluginBundle:Pricing`](pricing.md)
0255. Add admin buttons `AppBundle:AdminButtons`

### `UIEvents::SUCCESS`

Event emitted when the results of an already filled form have to be shown to the user (or an admin viewing a specific enrollment)

 * Form entity (from GUID in the URL)
 * Enrollment entity (from GUID in the URL)
 * Disable edits (boolean; if the form has to be shown with all fields disabled or not)
 * Submitted form (the symfony form that will be presented to the user; available after the `AppBundle:DefaultEvents` handler has been called)
 * List of templates to render

255. Re-emits the `UIEvents::SUCCESS` event with the form entity swapped out for the one that was used to submit the form, and copies the templates and form from this new event to the old event **Stops propagation** [`PluginBundle:RoleDifferentiation`](role_differentiation.md)
0. Build and show disabled form if there are no other templates added yet `AppBundle:DefaultEvents`
    * Emit `FormEvents::BUILD` with form entity and enrollment from `UIEvents::FORM`
    * Emit `FormEvents::SETDATA` with the form entity and enrollment from `UIEvents::FORM` and the form built from the formbuilder from `FormEvents::BUILD`
0253. Add payment information [`PluginBundle:Pricing`](pricing.md)
0255. Add admittance QR-code [`PluginBunde:AdmissionCheck`](admission_check.md)

### `FormEvents::BUILD`

Event emitted to build the submission form. Emitted by `AppBundle:DefaultEvents` listeners for `UIEvents::FORM` and `UIEvents::SUCCESS`

 * Form entity (The form entity present on the `UIEvents::FORM` or the `UIEvents::SUCCESS` event when the `AppBundle:DefaultEvents` handler gets called)
 * Form builder (Symfony form builder that will be used to build the symfony form that will be presented to the user. In case the disable edits is set on the `UIEvents::SUCCESS` events, this form builder will be set as disabled.)
 
When the event is emitted by the `UIEvents::SUCCESS` `AppBundle:DefaultEvents` handler, the form data from the enrollment data will have been loaded in the form builder.

0. Includes the form template and calls the returned closure with the form builder [`PluginBundle:FormTemplate`](form_template.md)
0. Adds all defined fields to the form builder [`PluginBundle:FormBuilder`](form_builder.md)
05. If a user is logged in, fills in their data for the `name` and `email` fields [`PluginBundle:PrefillUserData`](prefill_user_data.md)
0255. Add a submit button if there isn't one yet `AppBundle:SubmitButton`

### `FormEvents::SETDATA`

Event emitted to add/change data on the submission form after it has been fully built. Emitted by `AppBundle:DefaultEvents` listeners for `UIEvents::FORM` and `UIEvents::SUCCESS`

 * Form entity (The form entity present on the `UIEvents::FORM` or the `UIEvents::SUCCESS` event when the `AppBundle:DefaultEvents` handler gets called)
 * Submitted form (As built by the form builder from the `FormEvents::BUILD` event; the symfony form that will be presented to the user)
 * Enrollment entity (Only when this event is emitted by the `AppBundle:DefaultEvents` handler in response to the `UIEvents::SUCCESS` event)

*No listeners*

### `FormEvents::SUBMIT`

Event emitted when the submission form gets submitted. 

 * Form entity (from GUID in the URL)
 * Submitted form (As built by the form builder from the `FormEvents::BUILD` event; the symfony form that will be presented to the user)
 * Type (will be `SubmitFormEvent::TYPE_CREATE` when the form is submitted to create a new enrollment, and `SubmitFormEvent::TYPE_EDIT` when the form is submitted to edit an enrollment)
 * Enrollment entity (An already persisted-but-not-yet-flushed enrollment if the type is `TYPE_CREATE`; the enrollment loaded from the GUID in the URL if the type is `TYPE_EDIT`)

If the submitted form has errors added to it during this event, it will not be stored in the database, but the form with errors
will be presented to the user.

9001. Add data present in disabled fields to the submission
255. Re-emits the `FormEvents::SUBMIT` event with the form entity swapped out for the differentiated one/the one that was used to submit the form **Stops propagation** [`PluginBundle:RoleDifferentiation`](role_differentiation.md)
11. Adds an error on the form when the user already has an enrollment for this form entity [`PluginBundle:UniqueUser`](unique_user.md)
10. Checks for duplicate entries in fields that are supposed to be unique and adds errors on the form for them. Creates/updates unique field data [`PluginBundle:UniqueFields`](unique_fields.md)
0. Includes the form template. If the returned object is an instance of `FormDefinitionInterface`, it calls the `handleSubmission` method with the submitted form and the enrollment entity [`PluginBundle:FormTemplate`](form_template.md)
04. Calculates and stores the total price for the enrollment [`PluginBundle:Pricing`](pricing.md)
    * Emits `PricingPaidAmountEditedEvent::EVENT_NAME` when the total price *changed* (so not on the initial submission)
05. Store/update the number of persons enrolled by this submission. Adds an error to the form if the number of enrolled persons goes over the limit and deny enrollments is enabled [`PluginBundle:CountEnrollments`](count_enrollments.md)
0100. Changes the form property of the enrollment to change the place where it will be stored [`PluginBundle:DivertEnrollments`](divert_enrollments.md)
0255. Sends a confirmation email for the enrollment. The data for the email is read from the form entity on the event [`PluginBundle:Email`](email.md)

### `PricingPaidAmountEditedEvent::EVENT_NAME`

Event emitted when the paid amount or the total price of an already submitted enrollment changes.
This event is emitted by [`PluginBundle:Pricing`](pricing.md)

0. Sends an email that the total price/paid amount changed. The data for the email is read from the form entity on the enrollment [`PluginBundle:Email`](email.md)
