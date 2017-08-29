'use strict';

var shell = require('shelljs'),
    fs = require('fs'),
    chalk = require('chalk');

module.exports = {
  fileExists: function (maybeFile) {
    try {
        return fs.statSync(maybeFile).isFile();
    } catch (err) {
      console.log(err);
      return false;
    }
  },

  dirExists: function (maybeDir) {
    try {
        return fs.statSync(maybeDir).isDirectory();
    } catch (err) {
      console.log(err);
      return false;
    }
  },

  expectDirectory: function (maybeDir) {
    if (!this.dirExists(maybeDir)) {
      shell.echo('Required directory not found: ' + maybeDir);
      shell.exit(1);
    }
  },

  quote: function (thing) {
    return '"' + thing + '"';
  },

  logSuccess: function (thing) {
    console.log(chalk.green(thing));
  },

  logError: function (err) {
    console.log(chalk.red(err));
  }
}
