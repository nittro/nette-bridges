#!/usr/bin/env bash

set -eu

BASE_DIR="$( cd "$(dirname "$0")" ; pwd -P )"

for f in ${BASE_DIR}/*.php; do
    echo -n "$( basename "$f" )... "
    /usr/bin/env php "$f"
    echo "OK"
done
