# Setup

## Manual setup

Refer to the [CONSOLE GUIDE](guides/console/SETUP.md).

## GitHub docs-deploy repo

* Click the [Use this template](https://github.com/chevere/docs-deploy/generate) button

![Create repo](guides/create-repo-template.png)

When done:

1. Go to **Secrets** under **Settings** from your new repo
2. Click on **New repository secret**
3. Provide the following repository **secrets**:

| Key                       | Description / example                                  |
| ------------------------- | ------------------------------------------------------ |
| REPO_DOCS                 | GitHub docs repo as `chevere/docs`                     |
| REPO_DOCS_ACCESS_TOKEN    | Token for the repo above                               |
| REPO_HOSTING              | GitHub repo for hosting as `chevere/chevere.github.io` |
| REPO_HOSTING_ACCESS_TOKEN | Token for the repo above                               |
| CNAME                     | Hostname* for docs `chevere.org`                       |

(*) Hostname: Make sure to CNAME your hostname to `<user>.github.io` as indicated in your GitHub pages repo.

## GitHub docs repo

* Create a new repository to use it as your **docs repo**. You can use [chevere/docs-template](https://github.com/chevere/docs-template) as base.

1. Go to **Secrets** under **Settings** from your new repo
2. Click on **New repository secret**
3. Provide the following repository **secrets**:

| Key                         | Description / example                         |
| --------------------------- | --------------------------------------------- |
| `REPO_DOCS_DEPLOY`          | Repo used for deploy as `chevere/docs-deploy` |
| `REPO_DOCS_DEPLOY_USERNAME` | Username with access to repo above            |
| `REPO_DOCS_DEPLOY_TOKEN`    | Token for the repo above                      |

> The personal access token must grant access to `repo` scope

### Automatic deployment on docs repo update

Check the [push-deploy.yml](https://github.com/chevere/docs-template/blob/main/.github/workflows/push-deploy.yml) workflow used to automatic trigger workflows on the deploy repo from the docs repo.
