#!/bin/bash

# Checks the syntax and executes PHP files.

set -u

exit_code=0
output="$1"
shift
for file in $@; do
  /usr/bin/php -l $file >> $output || exit_code=1
done

exit $exit_code
