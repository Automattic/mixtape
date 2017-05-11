#!/usr/bin/env bash

set -e;

usage() {
  echo "Usage:" 1>&2;
  echo "    $0 <prefix> <destination>" 1>&2;
  echo "" 1>&2;
  echo "Params:" 1>&2;
  echo "    prefix          a valid class prefix (e.g. Foo_Mixtape)" 1>&2;
  echo "    destination     path where the prefixed lib goes" 1>&2;
  exit 1;
}

if [ -z "$1" ]; then
  echo "<class prefix> is missing";
  usage
fi

if [ -z "$2" ]; then
  echo "<library destination> is missing";
  usage
fi

WORKING_DIR=`pwd`;

# echo $WORKING_DIR

destination=$2;
prefix=$1;

if [ ! -d "$destination" ]; then
  echo "$destination is missing, creating...";
  mkdir -p $destination;
fi

cd $destination;
destination_dir=`pwd`;
cd $WORKING_DIR;

if [ ! -d "$destination_dir" ]; then
  echo "$destination_dir is missing";
fi

echo "Generating new project with the following"
echo "lib_dir         = ${WORKING_DIR}"
echo "destination_dir = ${destination_dir}"
echo "prefix          = ${prefix}"

php ./scripts/new_project.php $prefix $WORKING_DIR $destination_dir
