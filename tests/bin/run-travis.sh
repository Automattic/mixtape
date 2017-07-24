#!/bin/bash

set -e

run_phpunit_for() {
  test_branch="$1";
  echo "Testing on $test_branch..."
  export WP_TESTS_DIR="/tmp/$test_branch/tests/phpunit"
  cd "/tmp/$test_branch/src/wp-content/plugins/$PLUGIN_SLUG"

  phpunit

  if [ $? -ne 0 ]; then
    exit 1
  fi
}

run_phpunit_for "wordpress-master"
run_phpunit_for "wordpress-latest"
run_phpunit_for "wordpress-previous"

exit 0
