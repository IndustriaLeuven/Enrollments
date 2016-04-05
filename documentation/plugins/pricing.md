# Plugin: pricing

The pricing plugin allows to track payment of the fees for an enrollment, and to show payment information to the user.

The price to be paid for an enrollment can be a static number or can be calculated from the data in the submitted form.
The payment information to be shown to the user can also be derived from the data submitted in the form.

The plugin also adds a filter to the enrollments list. This filter can be used to select enrollments that are paid, or those that are unpaid.
A quick action to mark all selected enrollments as paid is also available in the enrollments list.

Partial payment can be indicated on the enrollment by entering the paid amount on the edit enrollment page.

## Formula

The formula is used to calculate the price for the event from the form data.
This is a required field if the plugin is enabled.
It is a [symfony expression language](http://symfony.com/doc/current/components/expression_language/syntax.html) expression,
which must result in a numeric output.

Because this expression is compiled to both javascript and PHP, only a subset of the syntax is supported:

* literals: strings, numbers, booleans
* objects: not supported
* array access: supported. When accessing an item in a sub-array, you must first make sure this sub array item exists.
  (Instead of using `if(formData['events']['diner'], ..., ...)`, use `if(formData['events'] and formData['events']['diner'], ..., ...)`.
  When this rule is not followed, a PHP error will occur when the sub-array does not exist, and the enrollment cannot be registered.
* operators: everything except `matches`, `~` (string concatenation, use the `concat()` function instead), array operators (`in` & `not in`) and `..` (range)

Instead of the ternary operator, the `if` function can be used.

The variable `formData` is an array containing all fields from the submitted form, in the same format as they will be stored in the database.
The variable `_locale` is a string containing the language code for the language of the page the user is visiting.

## Payment expression

The payment expression is used to show information to the user on how to execute the payment.
This field is optional, if it is left empty no payment information box will be shown to the user.
It is a [symfony expression language](http://symfony.com/doc/current/components/expression_language/syntax.html) expression,
which must result in a string output.

Because this expression is compiled to both javascript and PHP, the same subset of syntax as for the [formula field](#formula) is allowed.

The variable `formData` is an array containing all fields from the submitted form, in the same format as they will be stored in the database.
The variable `totalPrice` is a number containing the result from the price calculation defined by the [formula field](#formula)
The variable `_locale` is a string containing the language code for the language of the page the user is visiting.

## `PluginDataBag`

### `Form` PluginDataBag

 * `formula`
 * `payment_expression`

### `Enrollment` PluginDataBag

 * `totalPrice`
 * `paidAmount`
