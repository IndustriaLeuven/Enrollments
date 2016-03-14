# Plugin: Count enrollments

The count enrollments plugin keeps track of the number of enrollments.
It shows the number of available places or the number of people on the waiting list to the user.

The plugin also adds a filter to the enrollments list. This filter can be used to select enrollments that are participants, 
or those that are on the waiting list.

## Max enrollments

The maximum number of people that are allowed to enroll.
Enrollments after this number is reached are placed on a waiting list.

## Deny enrollments

Instead of placing people on the waiting list, deny all further enrollments.

## Count expression

The expression is used to calculate the number of people that enroll in the event by this submission.
It is a [symfony expression language](http://symfony.com/doc/current/components/expression_language/syntax.html) expression,
which must result in an integral output.

The full expression language syntax is supported.
The variable `formData` is an array containing all fields from the submitted form, in the same format as they will be stored in the database.

> **Warning:** When accessing an item in a sub-array, you must first make sure this sub array item exists.
> (Instead of using `if(formData['events']['diner'], ..., ...)`, use `if(formData['events'] and formData['events']['diner'], ..., ...)`.
> When this rule is not followed, a PHP error will occur when the sub-array does not exist, and the enrollment cannot be registered.
