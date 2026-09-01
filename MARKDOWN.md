# Markdown

This system requires a markdown repo. Use [chevere/docs](https://github.com/chevere/docs/) as example/base.

## Filesystem

* Parses folders containing `.md`
* Works with N sub-levels
* `README.md` for indexes.
* `file-1.md` is automatic named to `File 1`.

| Structure                                                                          | Navigation       | Sidebar  |
| ---------------------------------------------------------------------------------- | ---------------- | -------- |
| [Files](tests/_resources/docs/files/)                                              | Dropdown         | Auto     |
| [Files with readme](tests/_resources/docs/files-readme/)                           | Link             | Combined |
| [Files with readme + sub-folders](tests/_resources/docs/files-readme-sub-folders/) | Link             | Combined |
| [Sub-folders](tests/_resources/docs/sub-folders/)                                  | Grouped dropdown | Auto     |

## Flags

Use `.php` files to customize the document names / links. **Must** use `<file>.md` to flag markdown documents and `folder/` to flag filesystem folders.

### `sorting.php`

The `sorting.php` file enables to customize the menu sorting for nav and sidebar.

```php
<?php

return [
    'README.md',
    'file-1.md',
    'folder-1/',
];
```

### `naming.php`

The `naming.php` file enables to customize the names used by the nodes (files and folders) and it will affect the link display for nav and sidebar.

```php
<?php

return [
    'README.md' => 'Intro',
    'file-1.md' => '-> File 1',
    'folder-1/' => '📁 Folder 1',
];
```

### `sidebar.php`

The `sidebar.php` file enables to override the sidebar.

```php
<?php

return 'auto';
```

## VuePress folder

The `.vuepress/` folder is required to config VuePress.

* `config-project.js`
  * Injects config values to `config.js`
* `enhanceApp-project.js`
  * Injects custom Vue components to the VuePress app
* `public/`
  * Use it for public content such as pics, icons and the manifest file. A file at `.vuepress/public/logo.svg` will resolve to `/logo.svg`
* `styles/`
  * Uses to store styling (CSS) properties.
