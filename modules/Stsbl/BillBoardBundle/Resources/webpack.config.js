// src/Stsbl/BillBoardBundle/Resources/webpack.config.js
let merge = require('webpack-merge');
let path = require('path');
let baseConfig = require(path.join(process.env.WEBPACK_BASE_PATH, 'webpack.config.base.js'));

let webpackConfig = {
    entry: {
        'css/billboard': './assets/less/billboard.less',
        'js/editor': './assets/js/editor.js',
        'js/image': './assets/js/image.js',
    },
};

module.exports = merge(baseConfig.get(__dirname), webpackConfig);
