# DocsDeploy

✨ Este repositorio permite publicar una documentación basada en [VuePress](https://vuepress.vuejs.org/).

- Genera **navbar** y **sidebar** automáticamente
- Usa convenciones en el sistema de archivos
- Simplifica el ordenar y nombrar enlaces

## Use as a template

Add this repository as a remote template:

```sh
git remote add template git@github.com:chevere/docs-deploy.git
```

Fetch remote template:

```sh
git fetch template
```

Merge remote template:

```sh
git merge template/main --allow-unrelated-histories
```

## Repositorio markdown

Este repositorio require de un repositorio markdown (ejemplo [chevere/docs](https://github.com/chevere/docs/)).

### Sistema de archivos

- Reconoce carpetas con archivos `.md`.
- Trabaja con n sub-niveles de profundidad.
- `README.md` denota indice.
- `file-1.md` se interpreta como `File 1`.

| Estructura                                                                           | Navegación                 | Barra lateral |
| ------------------------------------------------------------------------------------ | -------------------------- | ------------- |
| [Archivos](tests/_resources/docs/files/)                                             | Lista desplegable          | Automática    |
| [Archivos con leeme](tests/_resources/docs/files-readme/)                            | Enlace                     | Combinada     |
| [Archivos con leeme y sub-carpetas](tests/_resources/docs/files-readme-sub-folders/) | Enlace                     | Combinada     |
| [Sub-carpetas](tests/_resources/docs/sub-folders/)                                   | Lista agrupada desplegable | Automática    |

### Banderas

Archivos `.php` se utilizan cuando se requieran personalizar los elementos contenidos en una carpeta.

> **Nota**: Declara solamente lo que necesites modificar.

🧐 Al usar banderas se **debe** usar `<archivo>.md` para los documentos y `carpeta/` para las carpetas. Cualquier otro formato **no será reconocido**.

#### `sorting.php`

El archivo `sorting.php` permite determinar el ordenamiento de los elementos. Afectará el orden en la barra de navegación y en la barra lateral.

```php
<?php

return [
    'README.md',
    'file-1.md',
    'folder-1/',
];
```

#### `naming.php`

El archivo `naming.php` permite determinar el nombre de los elementos. Afectará el text en la barra de navegación y en la barra lateral.

```php
<?php

return [
    'README.md' => 'Intro',
    'file-1.md' => '-> File 1',
    'folder-1/' => '📁 Folder 1',
];
```

#### `sidebar.php`

El archivo `sidebar.php` permite insertar manualmente la barra lateral. Esto se debe usar como último recurso, especialmente cuando se usa una estructura de sistema de archivos no aceptada en las convenciones de este proyecto.

```php
<?php

return 'auto';
```

### Carpeta VuePress

La carpeta `/.vuepress/` en el repositorio markdown se require para configurar VuePress. Se usa de la siguiente manera:

- `config-project.js`
  - Inyectara valores de configuración a `config.js` (usada por VuePress).
- `public/`
  - Para archivos públicos como logos, iconos y manifest. Un archivo en `/.vuepress/public/logo.svg` estará disponible en `/logo.svg` al publicar el sistema.
- `styles/`  
  - Para indicar configuraciones referente al estilo (CSS).

## Deploying

### GitHub

Requires a Github repository for hosting (see Github pages).

Require Secrets on deploy repo (your fork of this repo):

- `REPO_DOCS` example [`chevere/docs`](https://github.com/chevere/docs/)
- `REPO_DOCS_ACCESS_TOKEN` token for the repo above
- `REPO_HOSTING` example `chevere/chevere.github.io`
- `REPO_HOSTING_ACCESS_TOKEN` token for the repo above
- `CNAME` example `chevere.org`

#### Automatic deploy

Require Secrets (on docs repo)

- `REPO_DOCS_DEPLOY` the repo used for deploy example `chevere/docs-deploy`
- `REPO_DOCS_DEPLOY_USERNAME` username with access to docs deploy repo
- `REPO_DOCS_DEPLOY_TOKEN` PAT for the username above

> The personal access token must grant access to `repo` scope

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
          curl -XPOST -u "${{ secrets.REPO_DOCS_DEPLOY_USERNAME }}:${{ secrets.REPO_DOCS_DEPLOY_TOKEN }}" -H "Accept: application/vnd.github.everest-preview+json" -H "Content-Type: application/json" https://api.github.com/repos/${{ secrets.REPO_DOCS_DEPLOY }}/dispatches --data '{"event_type": "build_application"}'
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
