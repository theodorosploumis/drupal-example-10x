const path = require('path');
const Encore = require('@symfony/webpack-encore');
if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

const drupalRoot = '../../web'.replace('/', path.sep);
const outputBasedir = 'sites/all/assets'.replace('/', path.sep);
const outputFolder = 'app'.replace('/', path.sep);
const outputDevFolder = 'app_dev'.replace('/', path.sep);

Encore
  .addEntry('app', './src/app.js')
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .enableBuildNotifications()
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = false;
  })
  ;

if (Encore.isProduction()) {
  Encore
    .setOutputPath(drupalRoot + path.sep + outputBasedir + path.sep + outputFolder)
    .setPublicPath(('/' + outputBasedir + '/' + outputFolder).replace(path.sep, '/'))
    .enableVersioning(true)
    .enableSourceMaps(false)
    ;
}
else {
  Encore
    .setOutputPath(drupalRoot + path.sep + outputBasedir + path.sep + outputDevFolder)
    .setPublicPath(('/' + outputBasedir + '/' + outputDevFolder).replace(path.sep, '/'))
    .enableVersioning(false)
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(true)
    ;
}

// SSR
const clientConfig = Encore.getWebpackConfig();

Encore.reset();

Encore
  .addEntry('app', './src/app.ssr.js')
  .disableSingleRuntimeChunk()
  .enableSourceMaps(false)
  .enableBuildNotifications()
  .configureBabelPresetEnv((config) => {
    config.useBuiltIns = false;
  })
  ;

if (Encore.isProduction()) {
  Encore
    .setOutputPath(drupalRoot + path.sep + outputBasedir + path.sep + outputFolder + '_ssr')
    .setPublicPath(('/' + outputBasedir + '/' + outputFolder + '_ssr').replace(path.sep, '/'))
    .enableVersioning(true)
    ;
}
else {
  Encore
    .setOutputPath(drupalRoot + path.sep + outputBasedir + path.sep + outputDevFolder + '_ssr')
    .setPublicPath(('/' + outputBasedir + '/' + outputDevFolder + '_ssr').replace(path.sep, '/'))
    .enableVersioning(false)
    .cleanupOutputBeforeBuild()
    ;
}

const serverConfig = Encore.getWebpackConfig();
serverConfig.target = 'node';

module.exports = [clientConfig, serverConfig];
