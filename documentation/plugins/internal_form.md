# Plugin: Internal form

The internal form plugin blocks users from accessing the form page, even when they know the URL.

The blocking is only applied to the form that is referenced by GUID in the URL.

There are two use-cases for this plugin:

- With the [`RoleDifferentiationPlugin`](role_differentiation.md), to protect internal forms that the role differentiation
  directs the user to based on his role
- With the [`DivertEnrollmentsPlugin`](divert_enrollments.md), to protect the target form from direct enrollments.

