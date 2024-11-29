// webpack.config.js

const Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');

Encore
    .setOutputPath('assets/build/')
    .setPublicPath('/build')
    .cleanupOutputBeforeBuild()
    //.enableBuildNotifications()
    .enableVersioning(Encore.isProduction())

    .addStyleEntry('appback', './assets/styles/appback.scss')
    .addStyleEntry('app', './assets/styles/app.scss')

    .enableSassLoader()

    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            plugins: [
                require('autoprefixer')
            ]
        };
    })

    .disableSingleRuntimeChunk()

    .enableSourceMaps(!Encore.isProduction())

    .addPlugin(new CopyWebpackPlugin({
        patterns: [
            {
                from: 'assets/build/images',
                to: '../../public/build/images',
                noErrorOnMissing: true 
            }
        ]
    }));

module.exports = Encore.getWebpackConfig();