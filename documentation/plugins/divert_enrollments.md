# Plugin: Divert enrollments

The divert enrollments plugin stores all enrollments that are submitted to a form in another form.

This plugin allows to hand out separate URLs with separate plugin configurations and separate plugin data,
while keeping all enrollments submitted through these forms together.

## Target form

This is the form where all enrollments submitted to this form will be stored.

Keep in mind that its GUID will be visible in the enrollment confirmation URL (as the enrollment is stored on this form),
and that - if the form is not meant to be filled out directly - appropriate measures should be taken.

Protecting from submissions directly to the target form is possible by:

- Disabling all plugins that create a form on the target form
- Using the [`InternalFormPlugin`](internal_form.md) on the target form

## `PluginDataBag`

### `Form` PluginDataBag

 * `target`

### `Enrollment` PluginDataBag

(None)
