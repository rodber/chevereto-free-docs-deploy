const project = require('./config-project')

project.theme = 'default-prefers-color-scheme';
project.head.push(
    ['meta', { 
        name: 'viewport',
        content: 'width=device-width, initial-scale=1.0'
    }]
);

if (!project.plugins) {
    project.plugins = [];
}
project.plugins.push(
    ['@vuepress/pwa', {
        serviceWorker: true,
        updatePopup: true
    }],
    ['@vuepress/medium-zoom', true]
);
if (!project.themeConfig.nav) {
    project.themeConfig.nav = [];
}
project.themeConfig.nav.push(
    ...require('./nav/en')
);
if (!project.themeConfig.sidebar) {
    project.themeConfig.sidebar = [];
}
project.themeConfig.sidebar = require('./sidebar/en');
module.exports = project