# DocsDeploy

> A VuePress documentation deploy generator.

## Conventions

> See working documentation for [chevere/docs](https://github.com/chevere/docs/) at [chevere.org](https://chevere.org/)

It follows the conventions of VuePress so `README.md` == index == `/` = `""`.

## VuePress

### `.vuepress/config-project.js`

The VuePress configuration. It will be injected to the actual build `config.js` used by VuePress.

### `.vuepress/public/`

For files like logos, icons and manifest. VuePress will map this folder to `/`. For example, `.vuepress/public/logo.svg` will be available at `/logo.svg`.

### `.vuepress/styles/`

Styles can be defined here.  

## Document Tree

```shell
tree ./ -a -I .git
./
├── list --> @1
│   ├── Cache.md
│   ├── Console.md
│   ├── Controller.md
│   ├── Filesystem.md
│   ├── Message.md
│   ├── Plugin.md
│   ├── Routing.md
│   ├── Str.md
│   ├── ThrowableHandler.md
│   ├── VarDump.md
│   └── Writer.md
├── list-index --> @2
│   ├── page-1.md
│   ├── page-2.md
│   ├── page-n.md
│   └── README.md
├── nlist --> @3
│   ├── folder-3-1
│   │   ├── page.md
│   │   └── another-page.md
│   ├── folder-3-2
│   │   └── page.md
├── nlist-index --> @4
│   ├── folder-4-1
│   │   └── another-page.md
│   ├── folder-4-2
│   │   └── page.md
│   ├── README.md
├── README.md
├── sortNav.php <-- Sorts nav
└── .vuepress
    ├── config-project.js
    ├── public
    │   ├── icons
    │   │   ├── android-chrome-192x192.png
    │   │   └── android-chrome-512x512.png
    │   ├── logo.svg
    │   └── manifest.json
    └── styles
        ├── index.styl
        └── palette.styl
```

| Path condition id | `README.md` | Folders | Navbar as      | Sidebar as                            |
| ----------------- | ----------- | ------- | -------------- | ------------------------------------- |
| @1 list           | No          | No      | Dropdown       | Auto for each single page             |
| @2 list-index     | Yes         | No      | Link           | Sidebar with children `['', <page>]`  |
| @3 nlist          | No          | 1 level | Same as @1 (*) | Same as @1                            |
| @4 nlist-index    | Yes         | 1 level | Same as @2     | A combined version @2 for each folder |

> **(*)** Case `@3` is **discouraged** (needs to implement nav groups, looks ugly at this time)