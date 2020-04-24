#!/bin/bash
set -e

uid=$(id -u)

if [ "$uid" != "0" ]
then
      usermod -u "$(stat -c %u /srv/app)" www-data || true
      groupmod -g "$(stat -c %g /srv/app)" www-data || true
else
    /bin/bash -c "$*"
fi
