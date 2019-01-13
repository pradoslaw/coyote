var webpack = require('webpack');
var path = require('path');
var env = require('node-env-file');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');

env(__dirname + '/.env');

function cdn(path) {
    return (typeof process.env.CDN != 'undefined' ? ('//' + process.env.CDN) : '') + path;
}

module.exports = {
    module: {
        loaders: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                loader: 'babel-loader',
                query: {
                    presets: ['env']
                }
            },
            {
                test: /\.(sass|scss|css)$/,
                loader: ExtractTextPlugin.extract(['css-loader', 'sass-loader'])
            },
            // {
            //     test: /\.(jpe?g|png|gif|svg)$/i,
            //     loader: 'file-loader',
            //     options: {
            //         context: '/',
            //         name: '[name].[ext]'
            //     },
            // }
        ],
        // rules: [
        //     {
        //         test: /\.(png|jpg|gif)$/,
        //         use: [
        //             {
        //                 loader: 'file-loader',
        //                 options: {},
        //             },
        //         ],
        //     },
        // ],
    },
    output: {
        path: path.join(__dirname, 'public'),
        filename: 'js/[name]-[chunkhash].js',
        chunkFilename: 'js/[name]-[chunkhash].js',
        publicPath: cdn('/')
    },
    externals: {
        jquery: "jQuery"
    },
    context: path.join(__dirname, 'resources/assets'),
    entry: {
        app: './js/app.js',
        posting: './js/posting.js',
        microblog: ['./js/pages/microblog.js', './sass/pages/microblog.scss'],
        forum: ['./js/pages/forum.js', './sass/pages/forum.scss'],
        wiki: ['./js/pages/wiki.js', './sass/pages/wiki.scss'],
        // job: ['./js/pages/job.js', './sass/pages/job.scss'],
        homepage: ['./js/pages/homepage.js', './sass/pages/homepage.scss'],
        pm: './js/pages/pm.js',
        profile: ['./js/pages/profile.js', './sass/pages/profile.scss'],
        'job-submit': './js/pages/job/submit.js',
        wikieditor: './js/plugins/wikieditor.js',
        main: './sass/main.scss',
        auth: './sass/pages/auth.scss',
        help: './sass/pages/help.scss',
        user: './sass/pages/user.scss',
        errors: './sass/pages/errors.scss',
        pastebin: './sass/pages/pastebin.scss',
        adm: './sass/pages/adm.scss',
        search: './sass/pages/search.scss'
    },
    plugins: [
        new webpack.HashedModuleIdsPlugin(),
        new webpack.optimize.CommonsChunkPlugin({name: "app", minChunks: 2, chunks: ['microblog', 'pm', 'forum', 'wiki', 'job', 'homepage', 'job-submit']}),

        // Extract all 3rd party modules into a separate 'vendor' chunk
        new webpack.optimize.CommonsChunkPlugin({
            name: 'vendor',
            minChunks: ({ resource }) => /node_modules/.test(resource),
        }),

        // make sure that hashes won't be changed in every compilation
        new webpack.optimize.CommonsChunkPlugin({
            name: 'runtime'
        }),

        // extract CSS to separate file
        // @todo hash powoduje, ze po kazdej zmianie odwiezane sa hashe WSZYSTKICH plikow CSS
        // natomiast uzycie chunkhahs powoduje, ze nie jest odswiezany hash na produkcji
        new ExtractTextPlugin({
            filename: 'css/[name]-[hash].css'
        }),

        // build JSON manifest with assets filenames so it can be read in laravel app
        new ManifestPlugin({
            fileName: 'manifest.json'
        })
    ]
};

if (process.env.NODE_ENV === 'production') {
    module.exports.plugins = module.exports.plugins.concat([
        new webpack.DefinePlugin({
            'process.env': {
                NODE_ENV: '"production"'
            }
        }),
        new webpack.optimize.UglifyJsPlugin({
            compress: {
                warnings: false
            }
        })
    ]);

    module.exports.devtool = '#hidden-source-map';
} else {
    module.exports.devtool = '#source-map';
}
