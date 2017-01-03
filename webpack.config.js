var webpack = require('webpack');
var path = require('path');

function assets(filename) {
    return path.join(__dirname, 'resources/assets/js/' + filename);
}

module.exports = {
    output: {
        filename: '[name].js',
        chunkFilename: '[chunkhash].js',
        publicPath: '/js/',
        // path: path.resolve(__dirname, 'js'),
    },
    resolve : {
        alias: {
            // bind version of jquery-ui
            // "jquery-ui$": path.resolve(__dirname, "public/js/jquery-ui.js"),
            // "jquery-ui": "jquery-ui.1.11.1/ui",
            // bind to modules;
            //modules: path.join(__dirname, "node_modules"),
        },

        root: [
            // path.resolve(path.join(__dirname, 'public/js')),
            // path.resolve(path.join(__dirname, 'node_modules/jquery-ui.1.11.1/ui'))
        ]
    },
    entry: {
        app: assets('app.js'),
        microblog: assets('pages/microblog.js'),
        forum: assets('pages/forum.js'),
        wiki: assets('pages/wiki.js'),
        job: assets('pages/job.js'),
        homepage: assets('pages/homepage.js'),
        'job-submit': assets('pages/job/submit.js'),
    },
    devtool: 'source-map',
    plugins: [
        // new webpack.optimize.UglifyJsPlugin({
        //     compress: {
        //         warnings: false
        //     }
        // })
        new webpack.optimize.CommonsChunkPlugin({name: "app", chunks: ["microblog", "forum", 'wiki', 'job']}),
    ]
};
