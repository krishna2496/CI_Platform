const host = "cip-api-service";
const port = 80;
module.exports = {
    chainWebpack: config => {
        config.module
            .rule("vue")
            .use("vue-loader")
            .loader("vue-loader")

        .tap(options => {
            options["transformAssetUrls"] = {
                img: "src",
                image: "xlink:href",
                "b-img": "src",
                "b-img-lazy": ["src", "blank-src"],
                "b-card": "img-src",
                "b-card-img": "img-src",
                "b-card-img-lazy": ["src", "blank-src"],
                "b-carousel-slide": "img-src",
                "b-embed": "src"
            };

            return options;
        });
    },

    publicPath: '/',

    lintOnSave: false,

    devServer: {
        host: host,
        port: port,
        https: false,
    },

    pluginOptions: {
        i18n: {
            locale: "en",
            fallbackLocale: "en",
            localeDir: "locales",
            enableInSFC: true,
            initImmediate: true
        }
    }
};
