# Deploy (console)

## Prepare

Copy `./config.sh.dist` to `./config.sh`.

```sh
cp config.sh.dist config.sh
```

Change the variables to match the target project.

Make `deploy.sh` executable:

```sh
chmod +x deploy.sh
```

## Commands

### Dev

Deploy to dev targeting documentation local dir:

```sh
./deploy.sh dev dir ~/git/chevereto/v4-docs
```

Deploy to dev targeting documentation repo (in config file):

```sh
./deploy.sh dev repo
```

### Production

Same as [dev](#dev) but change `dev` for `prod`.
