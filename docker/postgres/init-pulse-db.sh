#!/usr/bin/env bash
set -euo pipefail

pulse_db="${PULSE_DB_DATABASE:-laravel_pulse}"

psql -v ON_ERROR_STOP=1 --username "${POSTGRES_USER}" --dbname "${POSTGRES_DB}" <<-EOSQL
    CREATE DATABASE ${pulse_db} OWNER "${POSTGRES_USER}";
EOSQL
