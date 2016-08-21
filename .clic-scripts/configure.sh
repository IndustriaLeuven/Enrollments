#!/usr/bin/env bash
set -e # Quit script on error

$CLIC application:variable:get "$CLIC_APPNAME" app/configured >/dev/null 2>/dev/null || $CLIC application:execute "$CLIC_APPNAME" reconfigure

mail_transport="$($CLIC application:variable:get "$CLIC_APPNAME" mail/transport)"

cat > app/config/parameters-clic.yml <<EOL
# Auto-generated by $($CLIC -V) at $(date), DO NOT EDIT.
# Run \`$CLIC application:execute "$CLIC_APPNAME" reconfigure\` to update these configuration variables.
parameters:
    database_driver:   pdo_mysql
    database_host:     $($CLIC application:variable:get "$CLIC_APPNAME" mysql/host --filter=json_encode)
    database_port:     ~
    database_name:     $($CLIC application:variable:get "$CLIC_APPNAME" mysql/database --filter=json_encode)
    database_user:     $($CLIC application:variable:get "$CLIC_APPNAME" mysql/user --filter=json_encode)
    database_password: $($CLIC application:variable:get "$CLIC_APPNAME" mysql/password --filter=json_encode)
    database_path:     ~

    mailer_transport:  $($CLIC application:variable:get "$CLIC_APPNAME" mail/transport --filter=json_encode)
    mailer_host:       $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/host --filter=json_encode; else echo '~'; fi)
    mailer_user:       $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/user --filter=json_encode; else echo '~'; fi)
    mailer_password:   $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/password --filter=json_encode; else echo '~'; fi)
    mailer_encryption: $(if [[ "$mail_transport" != "mail" ]]; then $CLIC application:variable:get "$CLIC_APPNAME" mail/encryption --filter=json_encode; else echo '~'; fi)
    mailer_from:       $($CLIC application:variable:get "$CLIC_APPNAME" mail/sender --filter=json_encode)

    locale:            en
    secret:            '$(pwgen -s 100)'

    oauth_client_server: $($CLIC application:variable:get "$CLIC_APPNAME" app/oauth/server --filter=json_encode)
    oauth_client_id:     $($CLIC application:variable:get "$CLIC_APPNAME" app/oauth/client_id --filter=json_encode)
    oauth_client_secret: $($CLIC application:variable:get "$CLIC_APPNAME" app/oauth/secret --filter=json_encode)

    authserver_base_uri:     "%oauth_client_server%"
    authserver_api_username: $($CLIC application:variable:get "$CLIC_APPNAME" app/api/username --filter=json_encode)
    authserver_api_password: $($CLIC application:variable:get "$CLIC_APPNAME" app/api/password --filter=json_encode)

    homepage_redirect: $(if [[ "$($CLIC application:variable:get \"$CLIC_APPNAME\" app/homepage_redirect)" != "." ]]; then $CLIC application:variable:get "$CLIC_APPNAME" app/homepage_redirect --filter=json_encode; else echo 'null'; fi)
EOL

if [[ ! -e app/config/parameters.yml ]]; then
cat > app/config/parameters.yml <<EOL
imports:
    - { resource: parameters-clic.yml }
EOL
fi

exec $CLIC application:execute "$CLIC_APPNAME" redeploy
