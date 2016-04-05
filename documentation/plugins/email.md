# Plugin: Mail

The mail plugin sends informative emails about the status of the submission to the email address that is present on the submission.

The `email` form field is used as email address to send all updates about the submission to.

There are 3 types of informative mails:

 * Enrollment confirmation: Sent right after the form has been submitted by the user
 * Enrollment paid: Sent when the enrollment has been marked as paid with the [Pricing plugin](pricing.md)
 * Enrollment partially paid: Sent when the enrollment has been marked as partially paid with the [Pricing plugin](pricing.md)

Both the email subject and the email body are passed through the [twig templating system](http://twig.sensiolabs.org).

The subject is plain-text, the body is HTML formatted. A plain-text version of the body is automatically generated.

## Twig

The twig renderer that is used to render the email is sandboxed: no files can be included, only whitelisted tags, filters and functions are allowed.

### Crash course

All text outside the delimiters `{% ... %}` and `{{ ... }}` is printed as-is.

The contents of `{{ ... }}` are evaluated as an expression. The result of that expression is printed.
The contents of `{% ... %}` are named tags, and control the logic of the template.

An example "partially paid" template:

```twig
<p>Hello {{ enrollment.data.name }},</p> {# Prints the value of the 'name' field of the enrollment #}
<p>Your enrollment was paid partially on {{ date()|date }}</p> {# Puts the result of the date() function through the date filter #}
{% set total_price = enrollment.pluginData.get('pricing').totalPrice %} {# Set a new variable to a value #}
{% set paid_amount = enrollment.pluginData.get('pricing').paidAmount %}
{% if previous_total_amount < total_price %}
    {# The contents of this block are only shown when the condition above is true. #}
    <p>The price for this event has raised from &euro;{{ previous_total_amount|number_format(2) }} {# Put the previous_total_amount variable through the number_format filter #}
     to &euro;{{ total_price|number_format(2) }}.</p>
{% endif %}
{% if previous_paid_amount < paid_amount %}
    <p>We received a partial payment of &euro;{{ (paid_amount - previous_paid_amount)|number_format(2) }}
    {# Parentheses ensure the result of the subtraction is put through the number_format filter. Without parenthesis, only the previous_paid_amount would have been put through number_format #}
    {% if previous_paid_amount > 0 %}
        Previously, you paid &euro;{{ previous_paid_amount|number_format(2) }}.
    {% endif %}
    </p>
{% endif %}
<p>You already paid &euro;{{ paid_amount|number_format(2) }} of the total price of &euro;{{ total_price|number_format(2) }}.</p>
<p>There is still &euro;{{ (total_price - paid_amount)|number_format(2) }} left to pay.</p>

<p>Sincerely,<br>
Industria vzw
</p>
```

Of course, there are other tags, filters and functions available.

1. Tags
    - [`do`](http://twig.sensiolabs.org/doc/tags/do.html)
    - [`if`](http://twig.sensiolabs.org/doc/tags/if.html)
    - [`for`](http://twig.sensiolabs.org/doc/tags/for.html)
    - [`set`](http://twig.sensiolabs.org/doc/tags/set.html)
    - [`spaceless`](http://twig.sensiolabs.org/doc/tags/spaceless.html)
    - [`verbatim`](http://twig.sensiolabs.org/doc/tags/verbatim.html)
2. Filters
    1. Array/string
        - [`join`](http://twig.sensiolabs.org/doc/filters/join.html)
        - [`split`](http://twig.sensiolabs.org/doc/filters/split.html)
        - [`length`](http://twig.sensiolabs.org/doc/filters/length.html)
        - [`keys`](http://twig.sensiolabs.org/doc/filters/keys.html)
        - [`first`](http://twig.sensiolabs.org/doc/filters/first.html)
        - [`last`](http://twig.sensiolabs.org/doc/filters/last.html)
        - [`slice`](http://twig.sensiolabs.org/doc/filters/slice.html)
        - [`merge`](http://twig.sensiolabs.org/doc/filters/merge.html)
        - [`sort`](http://twig.sensiolabs.org/doc/filters/sort.html)
        - [`reverse`](http://twig.sensiolabs.org/doc/filters/reverse.html)
        - [`replace`](http://twig.sensiolabs.org/doc/filters/replace.html)
        - [`batch`](http://twig.sensiolabs.org/doc/filters/batch.html)
    2. Numbers
        - [`abs`](http://twig.sensiolabs.org/doc/filters/abs.html)
        - [`number_format`](http://twig.sensiolabs.org/doc/filters/number_format.html)
        - [`round`](http://twig.sensiolabs.org/doc/filters/round.html)
    3. Text
        - [`convert_encoding`](http://twig.sensiolabs.org/doc/filters/convert_encoding.html)
        - [`escape`](http://twig.sensiolabs.org/doc/filters/escape.html)
        - [`raw`](http://twig.sensiolabs.org/doc/filters/raw.html)
        - [`format`](http://twig.sensiolabs.org/doc/filters/format.html)
        - [`striptags`](http://twig.sensiolabs.org/doc/filters/striptags.html)
        - [`trim`](http://twig.sensiolabs.org/doc/filters/trim.html)
        - [`title`](http://twig.sensiolabs.org/doc/filters/title.html)
        - [`capitalize`](http://twig.sensiolabs.org/doc/filters/capitalize.html)
        - [`lower`](http://twig.sensiolabs.org/doc/filters/lower.html)
        - [`upper`](http://twig.sensiolabs.org/doc/filters/upper.html)
        - [`url_encode`](http://twig.sensiolabs.org/doc/filters/url_encode.html)
    4. Other
        - [`default`](http://twig.sensiolabs.org/doc/filters/default.html)
        - [`date`](http://twig.sensiolabs.org/doc/filters/date.html)
3. Functions
    - [`attribute`](http://twig.sensiolabs.org/doc/functions/attribute.html)
    - [`constant`](http://twig.sensiolabs.org/doc/functions/constant.html)
    - [`cycle`](http://twig.sensiolabs.org/doc/functions/cycle.html)
    - [`range`](http://twig.sensiolabs.org/doc/functions/max.html)
    - [`max`](http://twig.sensiolabs.org/doc/functions/max.html)
    - [`min`](http://twig.sensiolabs.org/doc/functions/min.html)
    - [`date`](http://twig.sensiolabs.org/doc/functions/date.html)
    - [`random`](http://twig.sensiolabs.org/doc/functions/random.html)
    - [`url`](http://symfony.com/doc/current/reference/twig_reference.html#url)

### Objects

For each template, variables are available to get enrollment-specific data from.

The `enrollment` variable is an instance of the `Enrollment` class.

#### class `Enrollment`

| Property     | Type            | Description |
| ------------ | --------------- | ----------- |
| `id`         | `string`        | The GUID of the enrollment, only available after the enrollment has been saved. `null` when sending enrollment confirmation. |
| `data`       | `array`         | The data from the submitted form. The array keys are field names, array values are data entered in the field. In case the field has sub-fields, the array values will be an array from sub-field name to sub-field value. |
| `pluginData` | `PluginDataBag` | Plugin configuration for this enrollment. |
| `form`       | `Form`          | The form entity where the enrollment has been saved on (not necessarily the form entity that generated the symfony form that was submitted by the user). |
| `createdAt`  | `DateTime`      | The moment the form was created. |

#### class `Form`

| Property     | Type            | Description |
| ------------ | --------------- | ----------- |
| `id`         | `string`        | The GUID of the form. |
| `name`       | `string`         | The name of the form. |
| `pluginData` | `PluginDataBag` | Plugin configuration for the form. |

#### class `PluginDataBag`

The `PluginDataBag` class is a wrapper around an array containing configuration of all enabled plugins.

| Method            | Type      | Description |
| ----------------- | --------- | ----------- |
| `has(pluginName)` | `boolean` | Checks if there is data for the plugin with name `pluginName` available in the data bag. |
| `get(pluginName)` | `mixed`   | The plugin data that is stored for the plugin with name `pluginName`. If the plugin is not available in the data bag, `null` is returned. If the plugin has no configuration options, but is enabled, `true` is returned. If the plugin has configuration options, usually an array of configuration options is returned. |

Reading the plugin data from a `Form` instance is probably not very useful, except maybe to determine if a plugin is enabled or not.
(And even then, you probably know which plugins you just enabled on the form, because you are on it's configuration page right now setting up this plugin.)

Reading the plugin data from an `Enrollment` instance is useful to read data from plugins that is variable per enrollment.
Only a couple of plugins store data on an `Enrollment` instance, the data that is available is documented on the documentation of that plugin.

> Final note: Undocumented plugin data is undocumented for a reason. Don't use them, they are probably not useful, and are subject to change.

## `PluginDataBag`

### `Form` PluginDataBag

 * `enroll`
 * `paid`
 * `paid_partially`

### `Enrollment` PluginDataBag

(None)
