const project = require('./config-project')

project.theme = 'default-prefers-color-scheme';
project.head.push(
    ['meta', {
        name: 'viewport',
        content: 'width=device-width, initial-scale=1.0'
    }]
);
if (!project.themeConfig) {
    project.themeConfig = {};
}
if (!project.themeConfig.nav) {
    project.themeConfig.nav = [];
}
if (!project.plugins) {
    project.plugins = [];
}
project.plugins.push(
    ['@vuepress/medium-zoom', true],
    ['seo', project.themeConfig.seo || {}]
);
if (project.themeConfig.nav_before) {
    project.themeConfig.nav.unshift(
        ...project.themeConfig.nav_before
    );
}
project.themeConfig.nav.push(
    ...require('./nav/en')
);
if (project.themeConfig.nav_after) {
    project.themeConfig.nav.push(
        ...project.themeConfig.nav_after
    );
}
if (!project.themeConfig.sidebar) {
    project.themeConfig.sidebar = [];
}
const prevExtendMarkdown = project.markdown && project.markdown.extendMarkdown;
project.markdown = Object.assign({}, project.markdown, {
    extendMarkdown: (md) => {
        if (prevExtendMarkdown) {
            prevExtendMarkdown(md);
        }
        if (md.disable) {
            md.disable('emoji');
        }
    }
});
project.chainWebpack = (config) => {
    config.module
      .rule('webp')
      .test(/\.webp$/)
      .use('file-loader')
      .loader('file-loader')
      .options({
        name: 'assets/img/[name].[hash:8].[ext]'
      });
}
project.themeConfig.sidebar = require('./sidebar/en');
module.exports = project
