const merge = require('webpack-merge');
const common = require('./webpack.common.js');
const glob = require('glob-all');
const path = require('path');
const OptimizeCSSAssetsPlugin = require("optimize-css-assets-webpack-plugin");
const TerserPlugin = require('terser-webpack-plugin');
const PurgeCssPlugin = require('purgecss-webpack-plugin');
const SentryPlugin = require("@sentry/webpack-plugin");
const webpack = require('webpack');

const plugins = [
  new PurgeCssPlugin({
    paths: glob.sync([
      path.join(__dirname, 'resources/views/**/*.twig'),
      path.join(__dirname, 'resources/js/components/**/*.vue'),
      path.join(__dirname, 'resources/js/**/*.ts'),
      path.join(__dirname, 'resources/js/**/*.js'),
    ]),
    safelist: {
      standard: [
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
        'pre',
        'kbd',
        'code',
        'video',
        'breadcrumb-fixed',
        'mention',
        'user-deleted',
        'strikeout',
        'ajax-loader',
        'link-broken',
        '[aria-label]',
        'x-placement',
        'tox-notifications-container',
        'editor-4play',
        'menu-group-service-operations',
        'menu-group-moderator-actions',
        'divider',
      ],
      deep: [
        /^logo/,
        /^language/,
        /^badge/,
        /^depth/,
        /^cm/,
      ],
      greedy: [
        /fake-(anchor|mention)/,
        /hire-me$/,
        /dropdown-menu/,
        /^tooltip/,
        /^bs-tooltip/,
        /^ps/,
        /^cool-lightbox/,
        /^tag/,
        /:not/,
        /^pre/,
        /^flatpickr/,
        /revive/,
        /^fa-/,
      ],
    },
  }),

  new webpack.EnvironmentPlugin(['FRONTEND_SENTRY_DSN', 'VAPID_PUBLIC_KEY', 'RELEASE']),
];

if (process.env.RELEASE) {
  plugins.push(new SentryPlugin({
    include: "./public",
    authToken: process.env.SENTRY_API_KEY,
    release: process.env.RELEASE,
    ignore: ["node_modules"],
    org: "coyote",
    project: "frontend",
  }));
}

module.exports = merge(common, {
  mode: "production",
  optimization: {
    namedChunks: true,
    minimize: true,
    usedExports: true,
    minimizer: [
      new TerserPlugin({
        extractComments: false,
        terserOptions: {
          output: {
            comments: false,
          },
        },
      }),
      new OptimizeCSSAssetsPlugin({}),
    ],
  },
  plugins,
});
