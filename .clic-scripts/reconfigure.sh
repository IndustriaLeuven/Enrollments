#!/usr/bin/env bash
set -e # Quit script on error

if ! tty -s; then
    printf "$(tput setab 1)You are not on a terminal, so we cannot ask you configuration questions.$(tput sgr0)\n"
    printf "Run $(tput setaf 2)$CLIC application:execute \"$CLIC_APPNAME\" reconfigure$(tput sgr0) in a terminal to configure interactively"
    exit 1
fi

$CLIC application:variable:set "$CLIC_APPNAME" mysql/database --description="Name of the database" --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" mysql/host --description="Hostname of the database" --if-not-global-exists --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" mysql/user --description="Username to connect to the database" --if-not-global-exists --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" mysql/password --description="Password of the database user"  --if-not-global-exists --default-existing-value

app_env=""
while [[ "$app_env" != "prod" && "$app_env" != "staging" ]]; do
    $CLIC application:variable:set "$CLIC_APPNAME" app/environment --description="Environment [prod|staging]" --default-existing-value --default=prod
    app_env="$($CLIC application:variable:get "$CLIC_APPNAME" app/environment)"
done;

mail_transport=""
while [[ "$mail_transport" != "mail" && "$mail_transport" != "smtp" && "$mail_transport" != "sendmail" && "$mail_transport" != "gmail" ]]; do
    $CLIC application:variable:set "$CLIC_APPNAME" mail/transport --description="Type of mail transport [mail|smtp|sendmail|gmail]" --if-not-global-exists --default-existing-value --default=mail
    mail_transport="$($CLIC application:variable:get "$CLIC_APPNAME" mail/transport)"
done;
if [[ "$mail_transport" != "mail" ]]; then
    $CLIC application:variable:set "$CLIC_APPNAME" mail/host --description="Hostname of the mail handler" --if-not-global-exists --default-existing-value
    $CLIC application:variable:set "$CLIC_APPNAME" mail/user --description="Username to connect to the mailhandler" --if-not-global-exists --default-existing-value
    $CLIC application:variable:set "$CLIC_APPNAME" mail/password --description="Password of the mail user" --if-not-global-exists --default-existing-value
    $CLIC application:variable:set "$CLIC_APPNAME" mail/encryption --description="Encryption type for mail [ssl|tls]" --if-not-global-exists --default-existing-value
fi;

$CLIC application:variable:set "$CLIC_APPNAME" mail/sender --description="Sender address of mails"  --default-existing-value

$CLIC application:variable:set "$CLIC_APPNAME" app/oauth/server --description="URL to the homepage of Authserver" --default-existing-value
printf "Create a new OAuth application at $(tput setaf 2)$($CLIC application:variable:get "$CLIC_APPNAME" app/oauth/server)/admin/oauth/clients$(tput sgr0)\n"
printf "Scopes: $(tput setaf 3)profile:realname, profile:groups, property:read & property:write$(tput sgr0)\n"
printf "Redirect URI: $(tput setaf 3)[URL to homepage of this application]/login/oauth\n"
$CLIC application:variable:set "$CLIC_APPNAME" app/oauth/client_id --description="OAuth Client ID" --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" app/oauth/secret --description="OAuth Client Secret" --default-existing-value

printf "Create a new API key at $(tput setaf 2)$($CLIC application:variable:get "$CLIC_APPNAME" app/oauth/server)/admin/apikeys$(tput sgr0)\n"
printf "Scopes: $(tput setaf 3)Group::read & Profile::read:email$(tput sgr0)\n"
$CLIC application:variable:set "$CLIC_APPNAME" app/api/username --description="API Key username" --default-existing-value
$CLIC application:variable:set "$CLIC_APPNAME" app/api/password --description="API Key password" --default-existing-value

$CLIC application:variable:set "$CLIC_APPNAME" app/homepage_redirect --description="URL to redirect users hitting the homepage of this application to. (Use . to leave empty)" --default-existing-value --default="."

$CLIC application:variable:set "$CLIC_APPNAME" app/rollbar_access_token --description="Project access token 'post_server_item' from the project at https://rollbar.com/" --default-existing-value --default=none

$CLIC application:variable:set "$CLIC_APPNAME" app/configured 1

exec $CLIC application:execute "$CLIC_APPNAME" configure
