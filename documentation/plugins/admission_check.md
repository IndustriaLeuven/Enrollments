# Plugin: Admission check

The admission check plugin allows to use enrollments as a means to control admittance to the event.

The user is shown a QR-code on the enrollment confirmation page, along with instructions to show it at the entrance.

At the entrance, the QR-code is scanned by an operator who has edit access to the enrollments for that event.
An event querying for the validity status of the enrollment is dispatched in which each plugin can put the validity as
far as it is concerned. Results are aggregated and displayed to the operator, showing a green screen when admittance should
be granted and a red screen when it should be denied. The reason(s) for this decision are shown to the operator.

Each enrollment can only be used once to gain admittance to the event. After admittance is granted for the first time,
a flag is set on the enrollment and subsequent requests will result in a red admittance denied screen. 

The plugin also adds a filter to the enrollments list. This filter can be used to select enrollments that are already
used to gain admittance, or those that are not yet used.

## `PluginDataBag`

### `Form` PluginDataBag

(None)

### `Enrollment` PluginDataBag

 * `used`
