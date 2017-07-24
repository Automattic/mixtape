#!/usr/bin/env bash

set -e

DB_NAME=${DB_NAME-'mixtape_test'}
DB_USER=${DB_USER-'root'}
DB_PASS=${DB_PASS-''}
DB_HOST=${DB_HOST-'localhost'}

WP_VERSION=${WP_VERSION-4.8}
WP_TESTS_DIR=${WP_TESTS_DIR-/tmp/wordpress-tests-lib}
WP_CORE_DIR=${WP_CORE_DIR-/tmp/wordpress/}

COMPOSER_SCRIPT=${COMPOSER_SCRIPT-'composer'}

THISDIR=`pwd`

if [[ $WP_VERSION =~ [0-9]+\.[0-9]+(\.[0-9]+)? ]]; then
	WP_TESTS_TAG="tags/$WP_VERSION"
else
	wget  -O /tmp/wp-latest.json "http://api.wordpress.org/core/version-check/1.7/"
	grep '[0-9]+\.[0-9]+(\.[0-9]+)?' /tmp/wp-latest.json
	LATEST_VERSION=$(grep -o '"version":"[^"]*' /tmp/wp-latest.json | sed 's/"version":"//')
	if [[ -z "$LATEST_VERSION" ]]; then
		echo "Latest WordPress version could not be found"
		exit 1
	fi
	WP_TESTS_TAG="tags/$LATEST_VERSION"
fi

install_wp() {
	mkdir -p $WP_CORE_DIR

	if [ $WP_VERSION == 'latest' ]; then
		local ARCHIVE_NAME='latest'
	else
		local ARCHIVE_NAME="wordpress-$WP_VERSION"
	fi

	wget -nv -O /tmp/wordpress.tar.gz https://wordpress.org/${ARCHIVE_NAME}.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C $WP_CORE_DIR

	wget -nv -O $WP_CORE_DIR/wp-content/db.php https://raw.github.com/markoheijnen/wp-mysqli/master/db.php
}

install_test_suite() {
	# portable in-place argument for both GNU sed and Mac OSX sed
	if [[ $(uname -s) == 'Darwin' ]]; then
		local ioption='-i .bak'
	else
		local ioption='-i'
	fi

	# set up testing suite
	mkdir -p $WP_TESTS_DIR
	cd $WP_TESTS_DIR
	svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/
	svn co --quiet https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/

	wget -nv -O wp-tests-config.php https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php
	sed $ioption "s:dirname( __FILE__ ) . '/src/':'$WP_CORE_DIR':" wp-tests-config.php
	sed $ioption "s/youremptytestdbnamehere/$DB_NAME/" wp-tests-config.php
	sed $ioption "s/yourusernamehere/$DB_USER/" wp-tests-config.php
	sed $ioption "s/yourpasswordhere/$DB_PASS/" wp-tests-config.php
	sed $ioption "s|localhost|${DB_HOST}|" wp-tests-config.php

	mkdir -p $WP_TESTS_DIR/data/themedir1
}

install_db() {
	# parse DB_HOST for port or socket references
	local PARTS=(${DB_HOST//\:/ })
	local DB_HOSTNAME=${PARTS[0]};
	local DB_SOCK_OR_PORT=${PARTS[1]};
	local EXTRA=""

	if ! [ -z $DB_HOSTNAME ] ; then
		if [ $(echo $DB_SOCK_OR_PORT | grep -e '^[0-9]\{1,\}$') ]; then
			EXTRA=" --host=$DB_HOSTNAME --port=$DB_SOCK_OR_PORT --protocol=tcp"
		elif ! [ -z $DB_SOCK_OR_PORT ] ; then
			EXTRA=" --socket=$DB_SOCK_OR_PORT"
		elif ! [ -z $DB_HOSTNAME ] ; then
			EXTRA=" --host=$DB_HOSTNAME --protocol=tcp"
		fi
	fi

	if ! mysql -u$DB_USER -p$DB_PASS $DB_NAME -e 'SELECT 1' 2>&1 > /dev/null; then
	    # create database
			echo 'Creating Database'
    	mysqladmin create $DB_NAME --user="$DB_USER" --password="$DB_PASS"$EXTRA
	fi
}

install_wp_phpcs() {
	thisdir=`pwd`
	sniff_dir=$thisdir/tests/bin/codesniffer_rules
	echo $sniff_dir
	if [ ! -d $sniff_dir ]; then
    echo "Cloning Sniffing Rules"
		git clone -b master https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git $sniff_dir
		git clone -b master https://github.com/wimg/PHPCompatibility $sniff_dir/PHPCompatibility
	fi
	php "$THISDIR/vendor/bin/phpcs" --config-set installed_paths $sniff_dir
}

composer_install() {
    cd $THISDIR && $COMPOSER_SCRIPT install
}

install_wp
install_test_suite
install_db
composer_install
install_wp_phpcs
