# Plugin: Role differentiation

The role differentiation plugin allows to show users a different form (or deny them access to the form)
based on his authentication data.

## Rules

Differentiation rules are applied from top to bottom.
The first rule that matches will immediately transfer control to the target form, and other rules will not be
executed anymore.

### Condition

The condition expression is a [symfony expression language](http://symfony.com/doc/current/components/expression_language/syntax.html) expression,
which must result in a boolean output.
The full expression language syntax is supported.

The function `is_anonymous()` returns true when there is no user logged in.
The function `is_authenticated()` returns true when there is a user logged in.
The function `has_role(role)` returns true when the logged-in user has a specific role.

The variable `user` contains the user object [`User`](../../src/AppBundle/Entity/User.php), or the string 'anon.'
The variable `roles` contains all roles the user has.

See the [symfony security expressions cookbook](https://symfony.com/doc/current/cookbook/expression/expressions.html#book-security-expressions)
for more information regarding security expressions.

All exportable [authserver](https://github.com/vierbergenlars/authserver) groups that the user is member of will be present
as a role. The role is built by uppercasing the techname of the group, and prefixing it with `ROLE_GROUP_`.

> E.g.: Group `%sysops` becomes role `ROLE_GROUP_%SYSOPS`, group `industria_members` becomes role `ROLE_GROUP_INDUSTRIA_MEMBERS`

### Target form

The form the user will be directed to when the condition matches.

All plugins will be called again on the target form, so take care not to create a rewrite loop.
Most plugins (except [`CountEnrollmentsPlugin`](count_enrollments.md) and [`DatePlugin`](date.md)) are never called on
the initial form, but only on the target form.

All data that is entered in the target form is saved to the enrollments list of the initial form

