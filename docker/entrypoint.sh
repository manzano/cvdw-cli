#!/bin/sh
set -e

envsubst < "/app/src/envs/env.tmpl.ini" > "/app/src/envs/.env"

exec "$@"