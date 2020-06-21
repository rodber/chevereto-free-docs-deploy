# !/usr/bin/env sh

set -e

rm -rf docs

git clone git@github.com:chevere/docs.git

cp -r .vuepress docs

php src/build.php

cp index.md docs/README.md

npm run build

cd docs/.vuepress/dist

echo 'chevere.org' > CNAME

git init
git add -A
git commit -m 'deploy'
git push -f git@github.com:chevere/chevere.github.io.git master

cd -