// webpack.config.js

const Encore = require('@symfony/webpack-encore');

Encore
    // Indiquez le chemin où Webpack Encore stockera les fichiers compilés
    .setOutputPath('assets/styles/build/')
    // Chemin public pour accéder aux fichiers compilés (dans les templates Symfony, par exemple)
    .setPublicPath('/styles/build')
    // Nettoie le répertoire de sortie entre chaque build
    .cleanupOutputBeforeBuild()
    // Active les notifications dans le terminal en cas d'erreur
    .enableBuildNotifications()
    // Génère un hash pour les fichiers compilés (utile pour le cache en production)
    .enableVersioning(Encore.isProduction())

    // Fichier d'entrée pour notre fichier SCSS principal
    .addStyleEntry('appback', './assets/styles/appback.scss')
    .addStyleEntry('app', './assets/styles/app.scss')

    // Active la prise en charge de Sass/SCSS
    .enableSassLoader()

    // Active la prise en charge de PostCSS (pour autoprefixer notamment)
    .enablePostCssLoader((options) => {
        options.postcssOptions = {
            plugins: [
                require('autoprefixer')
            ]
        };
    })

    // Fichiers JavaScript
    //.addEntry('app', './assets/app.js') // Exemple d'entrée pour du JS

    // Configuration de Babel (pour ES6/ES7)
    /*.configureBabel((babelConfig) => {
        babelConfig.presets.push('@babel/preset-env');
    }, {
        useBuiltIns: 'usage', // Polyfill automatique des fonctionnalités utilisées
        corejs: 3
    })*/

    // Permet d'optimiser les images (à ajouter uniquement si besoin)
    //.enableImageLoader()

    .disableSingleRuntimeChunk()

    // Active le mode source map pour déboguer plus facilement en dev
    .enableSourceMaps(!Encore.isProduction())
;

// Exporte la configuration pour Webpack
module.exports = Encore.getWebpackConfig();