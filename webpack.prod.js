const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");

module.exports = merge(common, {
    mode: "production",
    optimization: {
        namedChunks: true,
        minimizer: [
            new UglifyJsPlugin({
                sourceMap: false
            }),
            new OptimizeCSSAssetsPlugin({})
        ]
    }
});
