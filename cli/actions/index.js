'use strict';

var shell = require('shelljs'),
    jsonfile = require('jsonfile'),
    fs = require('fs'),
    path = require('path'),
    util = require('./../util'),
    logSuccess = util.logSuccess,
    logError = util.logError,
    dirname = path.dirname,
    quote = util.quote;

var npmPackageRoot = dirname(dirname(dirname(fs.realpathSync(__filename))));

var doFreshGitCheckout = function () {
  var mixtapeTempPath = 'tmp/mt';
  var mixtapeRepo = 'https://github.com/Automattic/mixtape/';
  // if (!shell.which('git')) {
  //   logError('No Git found. Exiting.');
  //   shell.exit(1);
  // }
  if (mixtapePath === mixtapeTempPath) {
    console.log('checking dir exists');
    if (!util.dirExists(mixtapePath)) {
      shell.mkdir('-p', mixtapePath);
      var cmd = 'git clone ' + quote(mixtapeRepo) + ' ' + quote(mixtapePath);

      if (shell.exec(cmd).code !== 0) {
        logError('Error cloning mixtape repo: ' + mixtapeRepo);
        shell.exit(1);
      }

      shell.cd(mixtapePath)
      if (shell.exec('git checkout master').code !== 0) {
        logError("Can't run git checkout command on " + mixtapePath);
        shell.exit(1);
      }
    }
    shell.cd(mixtapePath);
    if (shell.exec('git fetch').code !== 0) {
      logError("Can't run git fetch command on " + mixtapePath);
      shell.exit(1);
    }
  }
};

var newProject = function (prefix, destination) {

  if (!prefix || !destination) {
    return false;
  }

  util.expectDirectory(destination);

  console.log("Generating prefixed project using:")
  console.log("lib_dir         = " + npmPackageRoot)
  console.log("destination_dir = " + destination)
  console.log("prefix          = " + prefix)

  generatePrefixedMixtapeLibrary();
};

var mixtapeFileName = 'mixtape.json';

var initNewProject = function (prefix, destination) {
  var scriptRoot = shell.pwd().stdout;
  var mixtapeFileTemplate = {
    prefix: prefix,
    destination: destination,
  }
  shell.cd(scriptRoot);
  jsonfile.writeFileSync(mixtapeFileName, mixtapeFileTemplate, {spaces: 2});
  logSuccess(mixtapeFileName + ' Generated:');
  shell.exec('cat ' + mixtapeFileName);
};

var buildMixtape = function () {
  var scriptRoot = shell.pwd().stdout,
      mixtapeTempPath = scriptRoot + '/tmp/mt',
      mixtapePath = npmPackageRoot;

  console.log('running from ' + scriptRoot)
  shell.cd(scriptRoot);

  util.expectDirectory(mixtapePath);

  if (!util.fileExists(mixtapeFileName)) {
    console.log('No ' + mixtapeFileName + ' found. Generate one using init');
    shell.exit(1);
  }

  shell.cd(scriptRoot);

  var mixtapeFile = jsonfile.readFileSync(mixtapeFileName);

  var currentSha = mixtapeFile.sha;
  var currentPrefix = mixtapeFile.prefix;
  var currentDestination = path.resolve(mixtapeFile.destination);

  logSuccess('============= Building Mixtape =============');

  if (!util.dirExists(currentDestination)) {
    shell.mkdir('-p', currentDestination);
  }

  if (!currentPrefix || !currentDestination) {
    return false;
  }

  util.expectDirectory(currentDestination);

  console.log('Running project script from ' + scriptRoot);

  console.log("Generating prefixed project using:")
  console.log("lib_dir         = " + npmPackageRoot)
  console.log("destination_dir = " + currentDestination)
  console.log("prefix          = " + currentPrefix)
  generatePrefixedMixtapeLibrary();
  logSuccess('Generation done!');
  shell.exit(0);
  // if (!newProject(currentPrefix, currentDestination)) {
  //   logError('Something went wrong with file generation, Exiting');
  //   shell.exit(1);
  // } else {
  //   logSuccess('Generation done!');
  //   shell.exit(0);
  // }
}

var generatePrefixedMixtapeLibrary = function () {
  var mixtapeFile = jsonfile.readFileSync(mixtapeFileName),
      prefix = mixtapeFile.prefix,
      prefixForFileName = 'class-'+ prefix.toLowerCase().replace(/_/g, '-')+ '-',
      destination = path.resolve(mixtapeFile.destination),
      scriptRoot = shell.pwd().stdout,
      mtPrefixRegExp = /MT/g;

  shell.cd(scriptRoot);
  shell.find('lib')
    .filter(function(file) { return file.match(/\.php$/); })
    .forEach(function (classTemplate) {
      var fileContents = fs.readFileSync(path.resolve(classTemplate), 'utf8'),
          prefixedContent = fileContents.replace(mtPrefixRegExp, prefix),
          destinationFilePath = classTemplate.replace('lib', destination)
            .replace('class-mt-', prefixForFileName);
      if (!util.dirExists(path.dirname(destinationFilePath))) {
        shell.mkdir('-p', path.dirname(destinationFilePath));
      }
      fs.writeFileSync(destinationFilePath, prefixedContent);
      console.log('- Using Template:  ' + classTemplate)
      console.log('- Generated:       ' + destinationFilePath)
      console.log('')
  });
};

module.exports = {
  build: buildMixtape,
  init: initNewProject,
  gen: generatePrefixedMixtapeLibrary,
  clean: function () {
    var mixtapeFile, destination;
    try {
      mixtapeFile = jsonfile.readFileSync(mixtapeFileName);
    } catch (e) {
      shell.exit(0);
    }

    destination = path.resolve(mixtapeFile.destination);
    if (!util.dirExists(destination)) {
      shell.exit(0);
    }
    shell.rm('-rf', destination);
  }
}
