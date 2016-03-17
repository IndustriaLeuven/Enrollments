# Plugin: Unique fields

The unique fields plugin allows to require that data in a submitted field is unique across all enrollments for the form.

## Fields

The fields are defined by the path to a value.
This path is defined by taking the array keys you need to traverse to get to a value, separated with a dot.

If the path refers to an array of fields instead of a value, the complete array has to be unique.

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
>
> For the path is `plus_one_data`, the values `{name: "Koen Van Kerckhoven", email: "koen.van.kerckhoven@industria.be"}`,
> `{name: "ABC", email: "koen.van.kerckhoven@industria.be"}` and `{name: "Koen Van Kerckhoven", email: "koen@industria.be"}`
> are regarded as different.
