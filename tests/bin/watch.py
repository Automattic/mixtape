#!/usr/bin/env python3
# coding: utf-8
import time
import subprocess
import logging
from os.path import realpath
from os.path import join
from os.path import dirname
from os.path import exists
from watchdog.observers import Observer
from watchdog.events import LoggingEventHandler
from watchdog.events import FileSystemEventHandler
from watchdog.events import PatternMatchingEventHandler


base_dir = realpath(dirname(dirname(dirname(__file__))))

tests_dir = join(base_dir, 'tests', 'unit', 'Mixtape')

lib_dir = join(base_dir, 'lib')

logger = logging.getLogger('PHPUnitEventHandler')

def on_created_or_modified(event):
    '''Runs any PHPUnit tests for this modified file.'''
    if not event.is_directory:
        file_path = event.src_path
        logger.info('Changed or Modified: %s' % file_path)
        if 'lib' in file_path:
            common_part = file_path.split('lib/')[-1]
            file_path = join(tests_dir, common_part).replace('.php', 'Test.php')
            logger.info('Test file: %s' % file_path)
        if exists(file_path):
            logger.info('Running PHPUnit')
            subprocess.run(['phpunit', file_path])
        else:
            logger.info('No PHPUnit tests found for %s' % file_path)


def main():
    paths = (tests_dir, lib_dir)
    patterns = ['*.php']
    event_handler = PatternMatchingEventHandler(patterns=patterns,
                                                ignore_directories=True)
    event_handler.on_created = on_created_or_modified
    event_handler.on_modified = on_created_or_modified
    observer = Observer()

    logger.info('Starting Watcher')
    for watcher_path in paths:
        observer.schedule(event_handler, watcher_path, recursive=True)
        for patttern in patterns:
            logger.info('Watching for %s files in %s' % (patttern, watcher_path) )

    observer.start()
    try:
        while True:
            time.sleep(0.2)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()


if __name__ == "__main__":
    logging.basicConfig(level=logging.INFO,
                        format='[%(asctime)s] - %(message)s',
                        datefmt='%Y-%m-%d %H:%M:%S')
    main()
