#!/usr/bin/env bash

dir=$(dirname $(readlink -f $0))
cd ${dir}
cd ..
vendor/bin/drush cron
