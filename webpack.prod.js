const {merge} = require('webpack-merge');
const common = require('./webpack.common.js');
const glob = require('glob-all');
const path = require('path');
const CssMinimizerPlugin = require("css-minimizer-webpack-plugin");
const PurgeCssPlugin = require('purgecss-webpack-plugin');
const SentryPlugin = require("@sentry/webpack-plugin");
const webpack = require('webpack');

const plugins = [
  new PurgeCssPlugin({
    paths: glob.sync([
      path.join(__dirname, 'app/**/*.php'),
      path.join(__dirname, 'resources/views/**/*.twig'),
      path.join(__dirname, 'resources/js/components/**/*.vue'),
      path.join(__dirname, 'resources/js/**/*.ts'),
      path.join(__dirname, 'resources/js/**/*.js'),
      path.join(__dirname, 'resources/feature/**/*.js'),
      path.join(__dirname, 'resources/feature/**/*.ts'),
      path.join(__dirname, 'survey/**/*.*'),
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
        'vue-notification-group',
        'vue-notification-wrapper',
        'neon-tab-active',
      ],
      deep: [
        /^logo/,
        /^language/,
        /^badge/,
        /^depth/,
        /^cm/,
      ],
      greedy: [
        /hire-me$/,
        /dropdown-menu/,
        /^ps/,
        /^tag/,
        /:not/,
        /^pre/,
        /^popover/,
        /revive/,
        /^fa-/,
      ],
    },
  }),

  new webpack.EnvironmentPlugin({
    'FRONTEND_SENTRY_DSN': null,
    'VAPID_PUBLIC_KEY': null,
    'RELEASE': null,
  }),
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
    chunkIds: 'named',
    minimize: true,
    usedExports: true,
    minimizer: [
      '...',
      new CssMinimizerPlugin(),
    ],
  },
  plugins,
});
