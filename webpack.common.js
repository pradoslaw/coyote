const path = require('path');
const webpack = require('webpack');
const ManifestPlugin = require('webpack-manifest-plugin');
const MiniCssExtractPlugin = require("mini-css-extract-plugin");
const CleanWebpackPlugin = require('clean-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const SVGSpritemapPlugin = require('svg-spritemap-webpack-plugin');

// const BundleAnalyzerPlugin = require('webpack-bundle-analyzer').BundleAnalyzerPlugin;

module.exports = {
  devtool: 'source-map', // slower but better
  module: {
    rules: [
      {
        test: /\.ts(x?)$/,
        exclude: /node_modules/,
        use: [
          {
            loader: 'babel-loader'
          },
          {
            loader: 'ts-loader',
            options: {
              appendTsSuffixTo: [/\.vue$/],
            }
          }
        ]
      },
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          loaders: {
            ts: 'babel-loader!ts-loader'
          }
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
              url: false
            },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
            }
          }
        ]
      },
      {
        test: /\.js$/,
        exclude: {
          test: /node_modules/
        },
        use: {
          loader: 'babel-loader',
          options: {
            cacheDirectory: true
          }
        }
      }
    ],
  },
  output: {
    path: path.join(__dirname, 'public'),
    filename: 'js/[name]-[contenthash].js',
    chunkFilename: 'js/[name]-[contenthash].js',
    publicPath: '/'
  },
  // externals: {
  //   vue: "Vue"
  // },
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
        // all vendors (except async) go to vendor.js
        vendor: {
          test: /[\\/]node_modules[\\/]/,
          name: "vendor",
          chunks: "all",
          priority: 1
        },
        // all common code across entry points
        common: {
          test: /\.(s?js|vue|ts)$/,
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
    mainFields: ['main', 'module'],
    extensions: [ '.ts', '.tsx', '.js', '.vue' ],

    alias: {
      '@': path.join(__dirname, 'resources/js'),
      vue: 'vue/dist/vue.esm.js'
    }
  },
  context: path.join(__dirname, 'resources'),
  entry: {
    core: './sass/core.scss',
    app: ['./js/app.js', './sass/app.scss'],
    legacy: './js/legacy.js',
    forum: ['./js/pages/forum.ts'],
    wiki: ['./js/pages/wiki.js'],
    job: ['./js/pages/job.js'],
    adm: './sass/pages/adm.scss'
  },
  plugins: [
    new VueLoaderPlugin(),

    new CleanWebpackPlugin(['public/js/*.*', 'public/css/*.*'], {}),
    // @see https://webpack.js.org/guides/caching/#module-identifiers
    new webpack.HashedModuleIdsPlugin(),

    new MiniCssExtractPlugin({
      filename: "css/[name]-[contenthash].css"
    }),

    new ManifestPlugin({
      fileName: 'manifest.json'
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
      'resources/images/logos/logo-rust.svg',
      'resources/images/logos/logo-go.svg',
      'resources/images/logos/logo-unity.svg',
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
        filename: path.join(__dirname, 'resources/sass/helpers/_sprites.scss')
      }
    }),

    // ,new BundleAnalyzerPlugin({analyzerPort: 3075})
  ]
};


