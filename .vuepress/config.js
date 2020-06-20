const { description } = require('../../package')

module.exports = {
    title: 'Chevere',
    description: 'A framework for building extensible applications',
    head: [
        ['link', { rel: 'icon', href: `/logo.svg` }],
        ['link', { rel: 'manifest', href: '/manifest.json' }],
        ['meta', { name: 'theme-color', content: '#23a8e0' }],
        ['meta', { name: 'apple-mobile-web-app-capable', content: 'yes' }],
        ['meta', { name: 'apple-mobile-web-app-status-bar-style', content: 'black' }],
        ['link', { rel: 'apple-touch-icon', href: '/logo.svg' }],
        ['link', { rel: 'mask-icon', href: '/logo.svg', color: '#3eaf7c' }],
        ['meta', { name: 'msapplication-TileImage', content: '/logo.svg' }],
        ['meta', { name: 'msapplication-TileColor', content: '#000000' }]
    ],
    plugins: [
        ['@vuepress/pwa', {
            serviceWorker: true,
            updatePopup: true
        }]
    ],
    themeConfig: {
        logo: '/logo.svg',
        repo: 'chevere/chevere',
        docsRepo: 'chevere/docs',
        // algolia: {
        //     apiKey: 'bfca2db324ab4f054a295c2f0e205176',
        //     indexName: 'prod_DOCS'
        // },
        editLinks: true,
        lastUpdated: true,
        nav: [
            {
                text: 'Get Started',
                link: '/get-started/',
            },
            {
                text: 'Architecture',
                link: '/architecture/',
            },
            {
                text: 'Application',
                link: '/application/',
            },
            {
                text: 'Components',
                ariaLabel: 'Components Menu',
                items: [
                    { text: 'Cache', link: '/components/cache' },
                    { text: 'Console', link: '/components/console' },
                    { text: 'Controller', link: '/components/controller' },
                    { text: 'Filesystem', link: '/components/filesystem' },
                    { text: 'Message', link: '/components/message' },
                    { text: 'Plugin', link: '/components/plugin' },
                    { text: 'Routing', link: '/components/routing' },
                    { text: 'Str', link: '/components/str' },
                    { text: 'ThrowableHandler', link: '/components/throwablehandler' },
                    { text: 'VarDump', link: '/components/vardump' },
                    { text: 'Writer', link: '/components/writer' },
                ]
            },
            {
                text: 'Examples',
                link: 'https://github.com/chevere/examples/',
            }
        ],
        sidebar: {
            '/get-started/': [
                {
                    title: 'Get Started',
                    collapsable: false,
                    children: [
                        '',
                        'overview',
                        'installing',
                    ]
                }
            ],
            '/architecture/': [
                {
                    title: 'Architecture',
                    collapsable: false,
                    sidebarDepth: 2,
                    children: [
                        '',
                        'coding-standard',
                        'immutability',
                    ]
                },
                {
                    title: 'Spec',
                    collapsable: false,
                    children: [
                        'components',
                        'interfaces',
                        'exceptions',
                        'testing',
                    ]
                },
                {
                    title: 'Development',
                    collapsable: false,
                    children: [
                        'workspace'
                    ]
                }
            ],
            '/application/': [
                {
                    collapsable: false,
                    children: [
                        'state',
                        'recommendations'
                    ]
                }
            ],
            '/components/': 'auto'
        }
    }
}