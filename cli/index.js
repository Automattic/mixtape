#!/usr/bin/env node

'use strict';

var program = require('commander'),
    actions = require('./actions'),
    version = '0.1.0',
    cliAppName = 'mixtape',
    appDescription = 'Model, Data Store, Data Transfer Object and REST API Controller Library for WordPress';

program.version(version)
  .name(cliAppName)
  .description(appDescription)

program.command('build', 'Build mixtape for development and plugin deployment (Note: Requires git)')
  .action(function (options) {
    actions.build(options);
  });

program
  .command('init [prefix] [folder]', 'Initalize a new mixtape-based project')
  .action(function (prefix, folder, options) {
  });


program.parse(process.argv);
