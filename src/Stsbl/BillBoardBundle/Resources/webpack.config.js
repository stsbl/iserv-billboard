// src/Stsbl/BillBoardBundle/Resources/webpack.config.js
let merge = require('webpack-merge');
let path = require('path');
let baseConfig = require(path.join(process.env.WEBPACK_BASE_PATH, 'webpack.config.base.js'));

let webpackConfig = {
    entry: {
        'css/billboard': './assets/less/billboard.less',
        'js/billboard_common': './assets/js/billboard_common.js',
        'js/billboard_editor': './assets/js/billboard_editor.js',
        'js/billboard_image': './assets/js/billboard_image.js',
    },
};

module.exports = merge(baseConfig.get(__dirname), webpackConfig);
