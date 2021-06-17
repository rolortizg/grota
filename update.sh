#!/bin/bash

composer install
drush config:import -y
drush updb -y
drush cr
