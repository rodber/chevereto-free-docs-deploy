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

Run `yarn dev` to preview.

```sh
./deploy.sh <mode=dev|prod> <resource=true|false>
```

Deploy to dev re-sourcing documentation repo:

```sh
./deploy.sh dev true
```

Deploy to production re-sourcing documentation repo:

```sh
./deploy.sh prod true
```
