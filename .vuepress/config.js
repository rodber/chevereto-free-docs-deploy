const project = require('./config-project')

if(!project.plugins) {
    project.plugins = [];
}
project.plugins.push(
    ['@vuepress/pwa', {
        serviceWorker: true,
        updatePopup: true
    }],
    ['@vuepress/medium-zoom', true]
);
project.themeConfig.nav = require('./nav/en')
project.themeConfig.sidebar = require('./sidebar/en')
module.exports = project