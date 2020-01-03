const Encore = require('@symfony/webpack-encore');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')

    .addEntry('main', './assets/js/main.js')
    .addEntry('home', './assets/js/home.js')

    .cleanupOutputBeforeBuild()
    .enableSingleRuntimeChunk()
    .enableBuildNotifications()
    .splitEntryChunks()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableIntegrityHashes(Encore.isProduction())

    .enableSassLoader()
    .enablePostCssLoader()

    .enableVueLoader(function(options) {
        // https://vue-loader.vuejs.org/options.html
    })

    .copyFiles({
        from: './assets/js/vendors',
        to: 'vendors/[path][name].[ext]',
    })
    .copyFiles({
        from: './assets/img/static',
        to: 'images/static/[path][name].[ext]',
    })

    .addPlugin(new BundleAnalyzerPlugin({
        analyzerMode: 'disabled',
        generateStatsFile: true,
    }))
    .addPlugin(new MomentLocalesPlugin({
        localesToKeep: ['en'],
    }))
;

module.exports = Encore.getWebpackConfig();
