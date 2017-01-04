var webpack = require('webpack');
var path = require('path');

module.exports = {
    output: {
        filename: '[name].js',
        chunkFilename: '[chunkhash].js',
        publicPath: '/js/'
    },
    context: path.join(__dirname, 'resources/assets/js'),
    entry: {
        app: './app.js',
        microblog: './pages/microblog.js',
        forum: './pages/forum.js',
        wiki: './pages/wiki.js',
        job: './pages/job.js',
        homepage: './pages/homepage.js',
        pm: './pages/pm.js',
        profile: './pages/profile.js',
        'job-submit': './pages/job/submit.js',
    },
    plugins: [
        new webpack.optimize.CommonsChunkPlugin({name: "app", minChunks: 2, chunks: ["microblog", "forum", 'wiki', 'job', 'homepage', 'job-submit']}),
    ]
};

if (process.env.NODE_ENV === 'production') {
    //
} else {
    module.exports.devtool = '#source-map';
}
