const project = require('./config-project')

project.plugins = [
    ['@vuepress/pwa', {
        serviceWorker: true,
        updatePopup: true
    }],
    ['@vuepress/medium-zoom', true],
]
project.themeConfig.nav = require('./nav/en')
project.themeConfig.sidebar = require('./sidebar/en')
module.exports = project