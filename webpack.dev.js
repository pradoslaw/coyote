const merge = require('webpack-merge');
const common = require('./webpack.common.js');

module.exports = merge(common, {
    mode: "development",
    devtool: 'source-map' // slower but better
    // devtool: 'cheap-module-eval-source-map' // faster
});
