var webpack = require('webpack');
var path = require('path');
var env = require('node-env-file');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const WebpackMd5Hash = require("webpack-md5-hash");
const CleanWebpackPlugin = require('clean-webpack-plugin');

env(__dirname + '/.env');

function cdn(path) {
    return (typeof process.env.CDN != 'undefined' ? ('//' + process.env.CDN) : '') + path;
}

module.exports = {
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: 'vue-loader'
            },
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {

                    loader: 'babel-loader',
                }
            },
            {
                test: /\.(sass|scss|css)$/,
                use: [
                    "style-loader",
                    MiniCssExtractPlugin.loader,
                    "css-loader",
                    "sass-loader"
                ]
            }
        ],
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
    optimization: {
        runtimeChunk: "single", // enable "runtime" chunk
        splitChunks: {
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name: "vendor",
                    chunks: "all",
                    priority: 1
                },
                utilities: {
                    test: /\.s?js$/,
                    minSize: 0,
                    name: "app",
                    chunks: "all",
                    priority: 0
                }
            }
        }
    },
    context: path.join(__dirname, 'resources/assets'),
    entry: {
        app: './js/app.js',
        posting: './js/posting.js',
        microblog: ['./js/pages/microblog.js', './sass/pages/microblog.scss'],
        forum: ['./js/pages/forum.js', './sass/pages/forum.scss'],
        wiki: ['./js/pages/wiki.js', './sass/pages/wiki.scss'],
        job: ['./js/pages/job.js', './sass/pages/job.scss'],
        homepage: ['./js/pages/homepage.js', './sass/pages/homepage.scss'],
        pm: './js/pages/pm.js',
        profile: ['./js/pages/profile.js', './sass/pages/profile.scss'],
        'job-submit': './js/pages/job/submit.js',
        wikieditor: './js/plugins/wikieditor.js',
        main: './sass/main.scss',
        auth: './sass/pages/auth.scss',
        help: './sass/pages/help.scss',
        'user-panel': './sass/pages/user.scss',
        errors: './sass/pages/errors.scss',
        pastebin: './sass/pages/pastebin.scss',
        adm: './sass/pages/adm.scss',
        search: './sass/pages/search.scss'
    },
    plugins: [

        new CleanWebpackPlugin(['public/js/*.*', 'public/css/*.*'], {} ),
        new MiniCssExtractPlugin({
            filename: "css/[name]-[contenthash].css"
        }),

        new WebpackMd5Hash(),

        // build JSON manifest with assets filenames so it can be read in laravel app
        new ManifestPlugin({
            fileName: 'manifest.json'
        }),
    ]
};
