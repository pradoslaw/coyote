var path = require('path');
const webpack = require('webpack');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CleanWebpackPlugin = require('clean-webpack-plugin');
const ConcatPlugin = require('webpack-concat-plugin');

const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');

// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

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
          {
            loader: 'css-loader',
            options: {
              sourceMap: true,
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
            },
          }
        ]
      }
    ],
  },
  output: {
    path: path.join(__dirname, 'public'),
    filename: 'js/[name]-[contenthash].js',
    chunkFilename: 'js/[name]-[contenthash].js',
    publicPath: '/'
  },
  externals: {
    jquery: "jQuery",
    vue: "Vue"
  },
  optimization: {
    runtimeChunk: "single",
    splitChunks: {
      cacheGroups: {
        // split async vendor modules to async chunks
        async: {
          test: /[\\/]node_modules[\\/]/,
          chunks: "async",
          minChunks: 1,
          minSize: 20000,
          priority: 2
        },
        // all vendors (except async) goes to vendor.js
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: "vendor",
          chunks: "all",
          priority: 1
        },
        // all common code across entry points
        common: {
          test: /\.s?js$/,
          minChunks: 2,
          name: "common",
          chunks: "all",
          priority: 0,
          enforce: true
        },
        default: false // overwrite default settings
      }
    }
  },
  resolve: {
    mainFields: ['main', 'module']
  },
  context: path.join(__dirname, 'resources/assets'),
  entry: {
    app: './js/app.js',
    posting: './js/posting.js',
    microblog: ['./js/pages/microblog.js'],
    // microblog: ['./js/pages/microblog.js', './sass/pages/microblog.scss'],
    forum: ['./js/pages/forum.js'],
    // forum: ['./js/pages/forum.js', './sass/pages/forum.scss'],
    wiki: ['./js/pages/wiki.js'],
    // wiki: ['./js/pages/wiki.js', './sass/pages/wiki.scss'],
    // job: ['./js/pages/job.js', './sass/pages/job.scss'],
    job: ['./js/pages/job.js'],
    homepage: ['./js/pages/homepage.js'],
    // homepage: ['./js/pages/homepage.js', './sass/pages/homepage.scss'],
    profile: ['./js/pages/profile.js'],
    // profile: ['./js/pages/profile.js', './sass/pages/profile.scss'],
    'job-submit': './js/pages/job/submit.js',
    wikieditor: './js/plugins/wikieditor.js',
    main: './sass/main.scss',
    // auth: './sass/pages/auth.scss',
    // help: './sass/pages/help.scss',
    // 'user-panel': './sass/pages/user.scss',
    // errors: './sass/pages/errors.scss',
    // adm: './sass/pages/adm.scss',
    // search: './sass/pages/search.scss'
  },
  plugins: [
    new CleanWebpackPlugin(['public/js/*.*', 'public/css/*.*'], {}),
    // @see https://webpack.js.org/guides/caching/#module-identifiers
    new webpack.HashedModuleIdsPlugin(),

    new MiniCssExtractPlugin({
      filename: "css/[name]-[contenthash].css"
    }),

    new ManifestPlugin({
      fileName: 'manifest.json'
    }),

    new ConcatPlugin({
      uglify: true,
      sourceMap: false,
      name: 'jquery-ui.js',
      fileName: 'js/jquery-ui.js',
      filesToConcat: [
        '../../node_modules/jquery-ui.1.11.1/ui/core.js',
        '../../node_modules/jquery-ui.1.11.1/ui/widget.js',
        '../../node_modules/jquery-ui.1.11.1/ui/mouse.js',
        '../../node_modules/jquery-ui.1.11.1/ui/resizable.js',
        '../../node_modules/jquery-ui.1.11.1/ui/sortable.js',
      ],
    }),

    new SVGSpritemapPlugin([
      // 'resources/images/logos/logo-python.svg',
      'resources/images/logos/logo-php.svg',
      'resources/images/logos/logo-java.svg',
      'resources/images/logos/logo-javascript.svg',
      'resources/images/logos/logo-cpp.svg',
      'resources/images/logos/logo-csharp.svg',
      'resources/images/logos/logo-css.svg',
      'resources/images/logos/logo-android.svg',
      'resources/images/logos/logo-lazarus.svg',
      'resources/images/logos/logo-postgresql.svg',
      ], {
      output: {
        filename: 'img/sprites-[contenthash].svg',

        svg: {
          // Disable `width` and `height` attributes on the root SVG element
          // as these will skew the sprites when using the <view> via fragment identifiers
          sizes: false
        },
        svgo: false
      },
      sprite: {
        generate: {
          // Generate <use> tags within the spritemap as the <view> tag will use this
          use: true,

          // Generate <view> tags within the svg to use in css via fragment identifier url
          // and add -fragment suffix for the identifier to prevent naming colissions with the symbol identifier
          view: '-fragment',

          // Generate <symbol> tags within the SVG to use in HTML via <use> tag
          symbol: true
        }
      },
      styles: {
        format: 'fragment',
        filename: path.join(__dirname, 'resources/assets/sass/components/_sprites.scss')
      }
    }),


    //
    // new BundleAnalyzerPlugin()
  ]
};


