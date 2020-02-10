const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const glob = require('glob-all');
const path = require('path');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const UglifyJsPlugin = require("uglifyjs-webpack-plugin");
const TerserPlugin = require('terser-webpack-plugin');
const PurgecssPlugin = require('purgecss-webpack-plugin');

module.exports = merge(common, {
  mode: "production",
  optimization: {
    namedChunks: true,
    minimizer: [
      // new UglifyJsPlugin({
      //   sourceMap: false
      // }),
      new TerserPlugin(),
      new OptimizeCSSAssetsPlugin({})
    ]
  },
  plugins: [
    new PurgecssPlugin({
      paths: glob.sync([
        path.join(__dirname, 'resources/views/**/*.twig'),
        path.join(__dirname, 'resources/assets/js/components/**/*.vue'),
      ]),
      whitelist: [
        'footer-bubble',
        'line-numbers',
        'token',
        'comment',
        'prolog',
        'doctype',
        'cdata',
        'punctuation',
        'namespace',
        'property',
        'tag',
        'boolean',
        'number',
        'constant'
      ],
      whitelistPatterns: [
        /^logo/,
        /^language/,
      ],
      whitelistPatternsChildren: [
        /hire-me$/
      ]
    })
  ]
});
