# !/usr/bin/env sh

set -e

if [ -f "./config.sh" ]; then
    . ./config.sh
    echo 'Using config.sh'
else
    . ./config.sh.dist
    echo 'Using config.sh.dist'
fi

if [ "$1" = 'prod' ] && [ "$2" != 'true' ]; then
    echo -n "Are you sure to going production without sourcing docs repo? It will source from ./docs (y/n)? "
    read answer
    if [ "$answer" != "${answer#[Nn]}" ]; then
        echo 'Ok no worries...'
        exit 1
    fi
fi

if [ "$2" = 'true' ]; then
    if [ -d "docs" ]; then
        cd docs
        if [ "$(git config --get remote.origin.url)" != "$GIT_DOCS" ]; then
            echo "Docs repo changed!"
            rm -rf -- "$(pwd -P)" && cd ..
            git clone $GIT_DOCS docs
        else
            git fetch --all
            git reset --hard origin/main
            cd -
        fi
    else
        git clone $GIT_DOCS docs
    fi
else
    echo 'Skipping docs sourcing...'
fi

if [ $# -eq 3 ]; then
    rm -rf docs/
    cp -a $3 docs/
fi

echo 'Copy .vuepress/ contents to docs/.vuepress/'
cp -rf .vuepress/. docs/.vuepress/

echo 'PHP: Build nav, sidebar, index.styl'
php build.php

yarn

if [ "$1" = 'dev' ]; then
    echo 'yarn: Dev VuePress'
    yarn dev
fi

if [ "$1" = 'prod' ]; then
    echo 'yarn: Build VuePress'
    yarn build

    cd docs/.vuepress/dist

    if [ -z "$CNAME" ]; then
        echo 'CNAME: None'
    else
        echo 'CNAME: created at docs/.vuepress/dist'
        echo $CNAME >CNAME
    fi

    git init
    git add -A
    git commit -m 'deploy'
    git branch -M main
    git push -f $GIT_HOSTING main

    cd -
fi
