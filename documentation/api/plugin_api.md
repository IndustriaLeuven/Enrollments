# Plugin API

Plugins are symfony event subscribers. The events they listen to and their position in the event chain are hard-coded.

## Events

### `PluginEvents`

These events are emitted when configuring a form in the admin view.

#### `PluginEvents::BUILD_FORM`

The [`PluginEvents::BUILD_FORM`](../../src/AppBundle/Event/PluginEvents.php) event allows to change the fields present in
the "create form" and "edit form" forms.
The use of this event is primarily to allow users to enable and configure the plugin.

When the event is emitted to build the "edit form" form, the existing configuration data for the plugin also has to be
set in the form (use the `data` option of the form)

The [`PluginConfigurationHelperTrait::buildPluginForm()`](../../src/PluginBundle/EventListener/PluginConfigurationHelperTrait.php)
helper method is available to automatically add an enable checkbox and a FormBuilder to add configuration fields to.
When the event is emitted to build the "edit form" form, the configuration data for the plugin is automatically set.
The data-structure that is stored in the form plugindata has to be the same structure as the form.
(Array keys have to have the same name as the form fields, recursively)

#### `PluginEvents::SUBMIT_FORM`
 
The [`PluginEvents::SUBMIT_FORM`](../../src/AppBundle/Event/PluginEvents.php) event allows to read the data the user submitted
with the "create form" and "edit form" forms.
The use of this event is primarily to save the configuration data users submitted for the plugin.

The [`PluginConfigurationHelperTrait::submitPluginForm()`](../../src/PluginBundle/EventListener/PluginConfigurationHelperTrait.php)
helper method is available to automatically save the plugin configuration data for plugins that are enabled, and to remove plugin
configuration data for plugins that have been disabled.
The data-structure is automatically stored in the form plugindata with the same structure as the form.


### `FormEvents`

These events are emitted when setting up and submitting an enrollment form.

> **WARNING**: These events are also emitted when an admin views or changes an enrollment form. User information will
> not be the same as the user that initially submitted the form.

#### `FormEvents::BUILD`

The [`FormEvents::BUILD`](../../src/AppBundle/Event/FormEvents.php) event allows to modify the enrollment form.

#### `FormEvents::SETDATA`

The [`FormEvents::SETDATA`](../../src/AppBundle/Event/FormEvents.php) event allows to modify data bound to the enrollment form.

Care has to be taken not to overwrite data that has already been submitted by the user.

#### `FormEvents::SUBMIT`

The [`FormEvents::SUBMIT`](../../src/AppBundle/Event/FormEvents.php) event allows to store data from a submitted enrollment form.

### `AdminEvents`

These events are emitted when using the admin interface.
They allow deep configuration of the pages containing the enrollments.

#### `AdminEvents::FORM_GET`

This event allows to add additional information to the "view form" page.

Templates are added in the order that `SubmittedFormTemplateEvent::addTemplate()` is called. Event listeners that
are registered earlier in the chain will have their templates more to the top.

The core data itself is also handled by a listener on this event with priority 0.

#### `AdminEvents::ENROLLMENT_SIDEBAR`

This event allows to add additional items to the sidebar that is visible throughout all enrollment views.

#### `AdminEvents::ENROLLMENT_LIST`

This event allows to filter the list of enrollments, add columns to the table (in both html and csv view)
and add filtering options (facets) to the heading of the list.

#### `AdminEvents::ENROLLMENT_BATCH`

This event allows to add actions that can be executed on multiple enrollments in one go to the enrollments list.
These actions do not allow any user input, enrollments that they apply to are only selected by a checkbox.

#### `AdminEvents::ENROLLMENT_GET`

This event allows to add additional information to the "view enrollment" page.

Templates are added in the order that `EnrollmentTemplateEvent::addTemplate()` is called. Event listeners that
are registered earlier in the chain will have their templates more to the top.

#### `AdminEvents::ENROLLMENT_EDIT`

This event allows to add additional fields to edit plugin data to the form on the "edit enrollment" page.

The [`EnrollmentEditHelperTrait::buildEnrollmentEditForm()`](../../src/PluginBundle/EventListener/EnrollmentEditHelperTrait.php)
helper method allows to easily add new fields to this form. Plugin data from the enrollment is automatically loaded in the fields
The data-structure that is stored in the enrollment plugindata has to be the same structure as the form.
(Array keys have to have the same name as the form fields, recursively)

#### `AdminEvents::ENROLLMENT_EDIT_SUBMIT`

This event allows to save data submitted in the form to edit plugin data on the "edit enrollment" page.

The [`EnrollmentEditHelperTrait::submitEnrollmentEditForm()`](../../src/PluginBundle/EventListener/EnrollmentEditHelperTrait.php)
helper method is available to automatically save the plugin data for the enrollment.
The data-structure is automatically stored in the enrollment plugindata with the same structure as the form.

#### `AdminEvents::ENROLLMENT_DELETE`

This event allows to remove plugin data attached to an enrollment when it is removed.

### `UIEvents`

These events allow to add extra templates when showing the enrollment form and enrollment confirmation pages
to a user.

#### `UIEvents::FORM`

This event allows to add additional information to the public "submit enrollment" page.

Templates are added in the order that `SubmittedFormTemplateEvent::addTemplate()` is called. Event listeners that
are registered earlier in the chain will have their templates more to the top.

#### `UIEvents::SUCCESS`

This event allows to add additional information to the public "enrollment confirmation" page.

Templates are added in the order that `SubmittedFormTemplateEvent::addTemplate()` is called. Event listeners that
are registered earlier in the chain will have their templates more to the top.

This event is also emitted during the `AdminEvents::ENROLLMENT_GET` event, to draw the templates for a submitted form
for the admin view.
