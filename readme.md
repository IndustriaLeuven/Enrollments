# Enrollments

Flexible, plugin-first enrollment system in PHP with [Authserver](https://github.com/vierbergenlars/authserver) integration.

All aspects of an enrollment form, both public and admin side,
are customizable with plugins that can be activated on a per-form basis.

## Installation

### Automated installation

You can quickly deploy this application with [clic](https://github.com/vierbergenlars/clic),
which provides an interactive installation and asks you for each configuration parameter.

To install the master branch: `clic application:clone git@github.com:IndustriaLeuven/Enrollments.git`

To update to the latest version, run the `update` script with clic.

### Manual installation

#### Download

The `master` branch should always be stable, you can download it as a zip archive or git clone it (recommended).

#### Configuration

Log in to Authserver as a super-admin and:

1. Create the groups `enrollments_admin` (admin access to the whole application) and `enrollments_backend`
(can view list of enrollment forms and has access to forms to which access has been granted specifically).
2. Create a new OAuth application with:
    * Redirect uri `{enrollments_root_url}/login/oauth`
    * At least access to scopes: `profile:username profile:realname profile:groups property:read property:write`
3. Create a new API key with at least access to scopes: `r_group` and `r_profile_email`

Create an `app/config/parameters.yml` from the `app/config/parameters.yml.dist` template and fill in the applicable
configuration parameters.

#### Dependencies

PHP dependencies are handled by [`composer`](https://getcomposer.org/),
these can be installed with a single `SYMFONY_ENV=prod composer install --no-dev -o` inside the project root.

To compile the bootstrap stylesheets, `less` is required. Less runs on  [`node.js`](https://nodejs.org/),
so install that one first. Then run `npm install` inside the project root to install `less`.

Then run the following commands to prepare the database and assets.

```bash
php app/console assets:install --env=prod
php app/console assetic:dump --env=prod
php app/console braincrafted:bootstrap:install --env=prod
php app/console doctrine:migrations:migrate --env=prod
```

#### Publishing the application

Only the `web/` directory should be publicly accessible, all requests that do not match a file in the `web/` directory
should be rewritten to `web/app.php` by the webserver. How to accomplish this depends on your webserver,
but a `.htaccess` file that accomplishes this is present in the `web/` folder.

### Development install

The development environment be created and set-up with vagrant:

```bash
vagrant up
```

The enrollments virtual machine is assigned the IP address `192.168.80.7`.
The web application is only available over http (port 80).

To log in, an Authserver authentication server is required.

* If you want a minimal installation of authserver to go along with the application, start its virtual machine too: `vagrant up authserver`.
It will automatically run the `master` version of [`vierbergenlars/authserver`](https://github.com/vierbergenlars/authserver),
and is configured to work out-of-the-box with the enrollments application.
* You can also bring along your own installation of authserver,
but you will have to update the parameters in `provisioning/parameters.yml.j2` to match your OAuth and admin API id's and passwords.






