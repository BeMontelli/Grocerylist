// webpack.config.js

const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('assets/styles/build/')
    .setPublicPath('/styles/build')
    .cleanupOutputBeforeBuild()
    //.enableBuildNotifications()
    .enableVersioning(Encore.isProduction())

    .addStyleEntry('appback', './assets/styles/appback.scss')
    .addStyleEntry('app', './assets/styles/app.scss')

    .enableSassLoader()

    // Active la prise en charge de PostCSS (pour autoprefixer notamment)
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            plugins: [
                require('autoprefixer')
            ]
        };
    })

    //.addEntry('app', './assets/app.js')

    // Configuration de Babel (pour ES6/ES7)
    /*.configureBabel((babelConfig) => {
        babelConfig.presets.push('@babel/preset-env');
    }, {
        useBuiltIns: 'usage', // Polyfill automatique des fonctionnalités utilisées
        corejs: 3
    })*/

    //.enableImageLoader()

    .disableSingleRuntimeChunk()

    .enableSourceMaps(!Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();