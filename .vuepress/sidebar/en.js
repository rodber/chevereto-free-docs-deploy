// module.exports = {}
module.exports = {
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