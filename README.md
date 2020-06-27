# DocsDeploy

> A VuePress documentation deploy template.

It takes a markdown docs repo and generates a VuePress app that can be configured to be automatically published to Github pages.

## Requirements

* A markdown docs repository (example [chevere/docs](https://github.com/chevere/docs/))

## Markdown docs conventions

### .vuepress (docs repo)

### `config-project.js`

The VuePress configuration. It will be injected to the actual build `config.js` used by VuePress.

### `public/`

For files like logos, icons and manifest. VuePress will map this folder to `/`. For example, `.vuepress/public/logo.svg` will be available at `/logo.svg`.

### `styles/`

Styles can be defined here.  

### Tree

```shell
tree ./ -a -I .git
./
├── list --> @1
│   ├── page-1.md
│   └── page-2.md
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
├── sortNav.php <-- To sort the nav
├── .github
│   ├── workflows/deploy.yml <-- For automatic deploy
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

## Deploying

### GitHub

Requirements:

* A Github repository for hosting (see Github pages)

Require Secrets (on deploy repo):

- `REPO_DOCS` example [`chevere/docs`](https://github.com/chevere/docs/)
- `REPO_HOSTING` example `chevere/chevere.github.io`
- `CNAME` example `chevere.org`

#### Automatic deploy

Require Secrets (on docs repo):

- `REPO_DEPLOY` the repo used for deploy example `chevere/docs-deploy`
- `PAT_USERNAME` username of the personal access token
- `ACCESS_TOKEN` value of the personal access token

Configure the docs repo to automatically trigger a new deploy by adding `.github/workflows/push-deploy.yml` in the documentation repo.

```yml
name: Push deploy
on: push
jobs:
  push-deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Push it
        run: |
          curl -XPOST -u "${{ secrets.PAT_USERNAME }}:${{ secrets.ACCESS_TOKEN }}" -H "Accept: application/vnd.github.everest-preview+json" -H "Content-Type: application/json" https://api.github.com/repos/${{ secrets.REPO_DEPLOY }}/dispatches --data '{"event_type": "build_application"}'
```

Change `on` to customize the triggering.

### Shell

Install dependencies:

```sh
yarn && composer install
```

Copy `config.sh.dist` to `config.sh`.

```sh
cp config.sh.dist config.sh
```

Change the variables to match the target project, then make `deploy.sh` executable:

```sh
chmod +x deploy.sh
```

Run `yarn dev` to preview, deploy running `./deploy.sh`.