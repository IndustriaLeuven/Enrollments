# Plugin: Form builder

The form builder plugin allows to create simple forms without having to write and upload code to build a form.

## Fields

Each form field is uniquely identified by its name. Every field also has a type, which determines the options that
are available for it.

### Name

The name of the form field. This name must be unique in the form, and is used as the storage key for the field.
It also is the default for the field label, if it is not changed in the options.

### Type

The type of the form field. 
A very important type is `Text`, which allows the user to enter one line of data.
Specialisations of the `Text` type are `Email`, `Integer` and `Url`, which hint the browser to which type of data is expected.
> **Important:** These hint are only an indication of the data to be expected, validation with a [constraint](#constraints) is still required.

The `Checkbox` type allows the user to make a binary choice, the `Choice` type allows for a more broad variety of choices.

The `Date`, `Time` and `DateTime` types let the user select a date and/or time via a drop-down menu.

### Show in enrollment list

Shows this field in the enrollments list for the form.

### Required

Hints the browser that this field is required to be filled in.
> **Important**: Validation with a `NotBlank` [constraint](#constraints) is still required to ensure no invalid data is stored.

### Disabled

This field will be present in the form, but will not be editable by the user.
> *Note:* Data submitted from disable form fields is discarded by the server, even when the field is re-enabled in javascript.

### Options

A variety of other options is available for a field, but most of these are specific to the field type,
and have to be entered here as an array. However, some are available for all types.

#### Global options

* `label`: Changes the label of the form field (defaults to a derivation of the field name)
* `label_attr`: Adds html attributes to the form label. It's an associative array with HTML attribute as a key.
* `data`: The data to show in this form field when the form is not filled. This should only be used to provide a default value
for a field that not a lot of people should have to change.
* `empty_data`: Data to be used when no value is submitted.

#### `Integer` type options

* `scale`: The amount of decimals allowed until the field rounds the submitted value. 
* `rounding_mode`: The way a number will be rounded. Defaults to round towards zero.
Other values can be selected from the [`IntegerToLocalizedStringTransformer`](http://api.symfony.com/2.8/Symfony/Component/Form/Extension/Core/DataTransformer/IntegerToLocalizedStringTransformer.html)
with the `constant()` function. (Like `constant('Symfony\Component\Form\Extension\Core\DataTransformer\IntegerToLocalizedStringTransformer::ROUND_UP')`)

#### `Choice` type options

> **Important:** Choices that are made should still be validated by the `Choice` [constraint](#constraints)

* `choices` **Required**: The choices the user can pick from. It's an associative array with label as a key, and stored value as a value.
* `expanded`: If set to true, radio buttons or checkboxes will be rendered instead of a select element.
* `multiple`: If set to true, the user will be able to select multiple options.
* `preferred_choices`: Array of preferred choices to put at the top of the select list (use the values of the `choices` array above).

#### `Date` type options

* `days`: List of days available to the day field. (1-31)
* `months`: List of months available to the month field. (1-12)
* `years`: List of years available to the year field. (-5 years - +5 years)
* `format`: The way and order dates are represented
* `widget`: 'choice', 'text', or 'single_text'. The way in which the field is rendered.

#### `Time` type options

* `hours`: List of hours available to the hour field (0-23)
* `with_minutes`: Set to false not to include minutes
* `minutes`: List of minutes available to the minutes field (0-59)
* `with_seconds`: Set to true to include seconds
* `seconds`: List of seconds available to the seconds field (0-59)
* `widget`: 'choice', 'text', or 'single_text'. The way in which the field is rendered.

#### `DateTime` type options

* `days`: List of days available to the day field. (1-31)
* `months`: List of months available to the month field. (1-12)
* `years`: List of years available to the year field. (-5 years - +5 years)
* `date_format`: The way and order dates are represented
* `date_widget`: 'choice', 'text', or 'single_text'. The way in which the field is rendered.
* `hours`: List of hours available to the hour field (0-23)
* `with_minutes`: Set to false not to include minutes
* `minutes`: List of minutes available to the minutes field (0-59)
* `with_seconds`: Set to true to include seconds
* `seconds`: List of seconds available to the seconds field (0-59)
* `time_widget`: 'choice', 'text', or 'single_text'. The way in which the field is rendered.

### Constraints

Constraints allow to validate data entered by users before it is stored, and show error messages
on the fields that failed validation.

A constraint has a type, which determines which validation takes place, and options that can configure
the details of the validation performed.

#### Global options

These options are available on all constraints.

* `message`: A custom message to be shown when validation fails. Defaults to a sensible value depending on the validator type.

#### `NotBlank`

Requires a field not to be blank.

#### `Blank`

Requires a field to be blank.

#### `Email`

Requires a field to contain an email address.

> *Note:* Mailaddresses can never be fully validated, unless a validation email has been sent to it.

#### `Length`

Requires a field to have a certain maximum and/or minimum length.

* `min`: Minimum length of the data in the field
* `minMessage`: Message to show when less than the minimum length has been entered
* `max`: Maximum length of the data in the field
* `maxMessage`: Message to show when more than the maximum length has been entered
* `exactMessage`: Message to show when `min = max` and the length of the entered data is not the same

#### `Url`

Requires a field to contain an URL.

> *Note:* The validity of an URL can only be fully validate by visiting it.

#### `Regex`

Validates the entered data with a regex.

* `pattern` **Required**: The regex pattern to check the entered data against. (remember, this should be a full regex pattern; use `/^(.*)$/` to validate the full message)
* `match`: Set to false to require the data not to match the pattern to pass the validation

#### `Choice`

Validates that the entered data is one of a set of valid choices

* `choices` **Required**: Array of choices that are valid
* `multiple`: If true, requires that the entered data is an array and all its values are in the `choices` array
* `multipleMessage` (if `multiple` is true): Message to show when one of the values is not a valid choice
* `min`: Require at least this many values to be present, if `multiple` is true
* `minMessage`: Message to show when the user chooses too few choices
* `max`: Require at most this many values to be present, if `multiple` is true
* `maxMessage`: Message to show when the user chooses too many choices

#### `Range`

Validates that a number is between a minimum and/or maximum.

You can also validate date ranges. Minimum and maximum values should be given as any date accepted by the
[`DateTime` constructor](http://www.php.net/manual/en/datetime.formats.php)

* `min` **Required**: Minimum value
* `minMessage`: Message to show when less than the minimum value
* `max` **Required**: Maximum value
* `maxMessage`: Message to show when more than the maximum value
* `invalidMessage`: Message to show when the value is not a number

#### `EqualTo`, `NotEqualTo`, `LessThan`, `LessThanOrEqual`, `GreaterThan`, `GreaterThanOrEqual`

Validates that a number matches the comparison with another value

You can also validate date ranges. Values to compare to should be given as any date accepted by the
[`DateTime` constructor](http://www.php.net/manual/en/datetime.formats.php)

* `value` **Required**: The value to compare to





