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

  shell.cd(npmPackageRoot);

  var result = shell.exec('php scripts/new_project.php ' + prefix + ' ' + quote(npmPackageRoot) + ' ' + quote(destination));
  if (result.code !== 0) {
      return false;
  }
  return true;
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
  console.log(mixtapeFile);

  if (!util.dirExists(currentDestination)) {
    shell.mkdir('-p', currentDestination);
  }

  util.expectDirectory(currentDestination);

  shell.cd(mixtapePath);

  console.log('Running project script from ' + scriptRoot);
  if (!newProject(currentPrefix, currentDestination)) {
    logError('Something went wrong with file generation, Exiting');
    shell.exit(1);
  } else {
    logSuccess('Generation done!');
    shell.exit(0);
  }
}

module.exports = {
  build: buildMixtape,
  init: initNewProject,
  gen: function () {
    var mixtapeFile = jsonfile.readFileSync(mixtapeFileName);
    var prefix = mixtapeFile.prefix;
    var destination = path.resolve(mixtapeFile.destination);
    var scriptRoot = shell.pwd().stdout;
    shell.cd(scriptRoot);
    var files = shell.find('lib').filter(function(file) { return file.match(/\.php$/); });
    files.forEach(function (f) {
      console.log(f)
      var fileContents = fs.readFileSync(f, 'utf8');
      var prefixedContent = fileContents.replace('MT', prefix);
      var prefixForFileName = 'class-'+ prefix.toLowerCase().replace('_', '-')+ '-';
      var ff = f.replace('lib', destination)
      console.log(ff.replace('class-mt-', prefixForFileName));
    })

  }
}
