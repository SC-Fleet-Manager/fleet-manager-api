const Encore = require('@symfony/webpack-encore');
const NormalModuleReplacementPlugin = require('webpack/lib/NormalModuleReplacementPlugin');
const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;
const MomentLocalesPlugin = require('moment-locales-webpack-plugin');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath(process.env.PUBLIC_URL ? process.env.PUBLIC_URL : '/build')
    .setManifestKeyPrefix('build/')

    .addEntry('main', './assets/js/main.js')
    .addEntry('home', './assets/js/home.js')

    // .enableStimulusBridge('./assets/controllers.json')

    .splitEntryChunks()
    .enableSingleRuntimeChunk()

    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .enableIntegrityHashes(Encore.isProduction())

    .enableSassLoader()
    .enablePostCssLoader()

    .enableTypeScriptLoader()
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
        generateStatsFile: !Encore.isProduction(),
    }))
    .addPlugin(new MomentLocalesPlugin({
        localesToKeep: ['en'],
    }))
    .addPlugin(new NormalModuleReplacementPlugin(
        /moment-timezone\/data\/packed\/latest\.json/,
        require.resolve('./assets/js/timezones.json')
    ))
;

module.exports = Encore.getWebpackConfig();
