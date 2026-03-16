# Deploy System

## Setup
1. Make sure you have the symbolic link in place:
   ```bash
   ln -s app/Lote/Deploy/Envoy.blade.php Envoy.blade.php
   ```

## Usage
Run deployment:
   ```bash
  php vendor/bin/envoy run deploy --commit="Commit message"
  ```
Additional methods:
```bash
deploy --commit="Commit message" --branch="main" --fix
```
- --fix — without composer update
- --branch — name of the branch, default is "staging"
- alias deploy="php vendor/bin/envoy run deploy" — add this to bashrc for alias

## Configuration
Environment variables needed:
- DEPLOY_PRODUCTION_SERVER
- DEPLOY_PRODUCTION_PATH
- DEPLOY_STAGE_SERVER
- DEPLOY_STAGE_PATH
- DEPLOY_STAGE_PORT
- DEPLOY_PRODUCTION_PORT

