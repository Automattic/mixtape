#!/bin/bash

# From Jetpack package

# If this is an NPM environment test we don't need a developer WordPress checkout

if [ "$WP_TRAVISCI" != "phpunit" ]; then
	exit 0;
fi

# This prepares a developer checkout of WordPress for running the test suite on Travis

mysql -u root -e "CREATE DATABASE wordpress_tests;"

CURRENT_DIR=$(pwd)

for WP_SLUG in 'master' 'latest' 'previous'; do
	echo "Preparing $WP_SLUG WordPress...";

	cd $CURRENT_DIR/..

	case $WP_SLUG in
	master)
		git clone --depth=1 --branch master git://develop.git.wordpress.org/ /tmp/wordpress-master
		;;
	latest)
		git clone --depth=1 --branch `php ./$PLUGIN_BASE_DIR/tests/bin/get-wp-version.php` git://develop.git.wordpress.org/ /tmp/wordpress-latest
		;;
	previous)
		git clone --depth=1 --branch `php ./$PLUGIN_BASE_DIR/tests/bin/get-wp-version.php --previous` git://develop.git.wordpress.org/ /tmp/wordpress-previous
		;;
	esac

	cp -r $PLUGIN_BASE_DIR "/tmp/wordpress-$WP_SLUG/src/wp-content/plugins/$PLUGIN_SLUG"
	cd /tmp/wordpress-$WP_SLUG

	cp wp-tests-config-sample.php wp-tests-config.php
	sed -i "s/youremptytestdbnamehere/wordpress_tests/" wp-tests-config.php
	sed -i "s/yourusernamehere/root/" wp-tests-config.php
	sed -i "s/yourpasswordhere//" wp-tests-config.php

	THISDIR="/tmp/wordpress-$WP_SLUG/src/wp-content/plugins/$PLUGIN_SLUG"

    cd $THISDIR && composer install

    sniff_dir=$THISDIR/tests/bin/codesniffer_rules
        echo $sniff_dir
        if [ ! -d $sniff_dir ]; then
        echo "Cloning Sniffing Rules"
            git clone -b master https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards.git $sniff_dir
        	git clone -b master https://github.com/wimg/PHPCompatibility $sniff_dir/PHPCompatibility
        fi
        cd $THISDIR && php "$THISDIR/vendor/bin/phpcs" --config-set installed_paths $sniff_dir

	echo "Done!";
done

exit 0;
