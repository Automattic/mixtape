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

program.command('build')
  .description('Build a project')
  .action(actions.build);

program.command('init <prefix> <folder>')
  .description('Initalize a new project. Provide a prefix and a relative lib folder.')
  .action(actions.init);

program.command('gen')
  .description('Generate a prefixed version of the library')
  .action(actions.gen);

program.command('clean')
  .description('clean up a build in this folder')
  .action(actions.clean);

program.parse(process.argv);
