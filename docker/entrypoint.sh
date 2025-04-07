#!/bin/sh
set -e

envsubst < "/app/src/envs/env.tmpl.ini" > "/app/src/envs/cvdw.env"

php src/cvdw configurar autoupdate --force=true --set-env=cvdw

/app/Executar_CVDW.sh

exec "$@"