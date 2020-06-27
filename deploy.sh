# !/usr/bin/env sh

set -e

. ./config.sh

if [ -d "docs" ]; then
    cd docs
    origin=$(git config --get remote.origin.url)
    if [ "$origin" != "$GIT_DOCS" ]; then
        echo "Docs repo changed!"
        rm -rf -- "$(pwd -P)" && cd ..
        git clone $GIT_DOCS
    else
        git reset --hard
        git pull
        cd -
    fi
else
    git clone $GIT_DOCS
fi

echo 'Copying .vuepress/ contents to docs/.vuepress/'
cp -rf .vuepress/. docs/.vuepress/

echo 'PHP: Building nav & sidebar'
php build.php

echo 'npm: Building VuePress'
npm run dev

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
git push -f $GIT_HOSTING master

cd -
