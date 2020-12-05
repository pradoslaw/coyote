const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const glob = require('glob-all');
const path = require('path');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require('terser-webpack-plugin');
const PurgecssPlugin = require('purgecss-webpack-plugin');

module.exports = merge(common, {
  mode: "production",
  optimization: {
    namedChunks: true,
    minimize: true,
    minimizer: [
      new TerserPlugin({
        extractComments: false,
        terserOptions: {
          output: {
            comments: false,
          }
        }
      }),
      new OptimizeCSSAssetsPlugin({})
    ]
  },
  plugins: [
    new PurgecssPlugin({
      paths: glob.sync([
        path.join(__dirname, 'resources/views/**/*.twig'),
        path.join(__dirname, 'resources/js/components/**/*.vue'),
        path.join(__dirname, 'resources/js/**/*.ts'),
      ]),
      whitelist: [
        'footer-bubble',
        'line-numbers',
        'line-numbers-rows',
        'token',
        'keyword',
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
        'constant',
        'modal-backdrop',
        'show',
        'fade',
        'fa-desktop',
        'fa-newspaper',
        'fa-bolt',
        'fa-column',
        'fa-shopping-cart',
        'fa-envelope',
        'fa-map-marker',
        'fa-star-half',
        'fa-chart-bar',
        'fa-wrench',
        'fa-key',
        'fa-minus',
        'fa-columns',
        'fa-user-slash',
        'pre',
        'code',
        'breadcrumb-fixed',
        'mention',
        'user-deleted',
        'strikeout',
        'ajax-loader',
        'link-broken'
      ],
      whitelistPatterns: [
        /^logo/,
        /^language/,
        /^badge/
      ],
      whitelistPatternsChildren: [
        /hire-me$/,
        /dropdown-menu/,
        /^tooltip/,
        /^bs-tooltip/,
        /^ps/,
        /^cool-lightbox/,
        /^tag/,
        /:not/,
        /^pre/,
        /^flatpickr/
      ]
    })
  ]
});
