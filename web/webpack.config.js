module.exports = {
    entry: "./js/src/index.js",
    output: {
        path: __dirname,
        filename: "./js/dist/bundle.js"
    },
    module: {
        loaders: [
            { test: /\.css$/, loader: "style-loader!css-loader" }
        ]
    }
};
