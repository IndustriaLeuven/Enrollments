# Plugin: Form template

The form template plugin allows to select a predefined form from the `app/Resources/forms` folder.

Additionally, it allows the user to set which fields are visible in the list of enrollments for this form.

## Form type

A form is defined by placing a PHP file in `app/Resources/forms` (or a subdirectory).

The file should not execute any code when it is included, but must return a `callable` that takes
a `Symfony\Component\Form\FormBuilderInterface` as first argument.
An array of [template specific settings](#using-template-specific-settings) is passed as second argument.
When the form is being built, this function will be called with the `FormBuilderInterface` where
the fields must be added to.

A submit button should not be added, as it will be added automatically to the end of the form.

### Manipulating submitted form data

By returning an object implementing `PluginBundle\Form\FormDefinitionInterface`,
form data can be manipulated before being sent to the database.
This function can also be used to implement additional validation rules, by adding new errors to the form.
If there are errors on the form, the submission will be aborted, and the error messages will be shown to the user.

The easiest way to use this functionality is to return an instance of `PluginBundle\Form\FormDefinition`.
This class takes the form definition callable described in the previous section as first argument,
and a submission handler as second argument.

The submission handler is a callable that takes three arguments.
The first argument is an instance of `Symfony\Component\Form\Form`, which has been bound to the submitted data and
has already passed the constraints attached to the fields.
The second argument is an instance of `AppBundle\Entity\Enrollment`, which has already received the data from the form
and any data set by event listeners with priority greater than 0 on the `FormEvents::SUBMIT` event.
The third argument is an array of [template specific settings](#using-template-specific-settings).

To change data to be stored in the database, `Enrollment::setData()` should be called with all data that
is desired to be stored in the database as submitted form fields.
(Plugin related data should be saved in the structure returned by `Enrollment::getPluginData()`)

### Using template-specific settings

Template specific settings are an extension point that allow the form administrator to provide additional
configuration to a form template to increase reusability of a template.

By returning an object implementing `PluginBundle\Form\FormDefinitionInterface`,
you can manipulate the settings page where the form administrator changes form configuration.

The easiest way to use this functionality is to return an instance of `PluginBundle\Form\FormDefinition`.
This class takes the form definition callable described in the previous section as first argument,
a submission handler as second argument (you can skip this argument by passing `null` instead of a callable)
and another form definition callable as third argument.

The callable passed as third argument receives a `FormBuilderInterface` where you can add
settings that will be shown in the form admin interface when this form template is in use.

## Admin enrollment list fields

The fields that are visible in the enrollments list for the form can be added here. If no fields
are defined, a yaml-encoded version of the form data will be shown in the enrollment list.

The fields are defined by the path to a value.
This path is defined by taking the array keys you need to traverse to get to a value, separated with a dot.

If a path points to a field that is not defined, an empty value will be shown.

> For example, when the stored data is
> ```
> {
>    name: "Lars Vierbergen",
>    email: "lars.vierbergen@industria.be",
>    plus_one: true,
>    plus_one_data: {
>       name: "Koen Van Kerckhoven",
>       email: "koen.van.kerckhoven@industria.be"
>    },
>    events: {
>        party: true,
>        reception: true
>    }
> }
> ```
> 
> * The path to get to value `"Lars Vierbergen"` is `name`
> * The path to get to value `"koen.van.kerckhoven@industria.be"` is `plus_one_data.email`
> * The path to determine if they are going to the reception is `events.reception`

## Form configuration

A form template may expose extra settings that can be used to customize the form template.
These settings will not immediately be available after selecting a template. The configuration with the template must be saved
first, extra settings will be available when editing the configuration afterwards.

## `PluginDataBag`

### `Form` PluginDataBag

 * `formType`
 * `admin_enrollment_list_fields`
 * `config`

### `Enrollment` PluginDataBag

(None)
