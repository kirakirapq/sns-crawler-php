# sns data collection system for php with laravel
## Overview
* This package is enable data collection of facebook and twitter and reddit data.
* This package was created on the assumption that cloud run, cloud schedule, secret manager will be used, but it is possible to use it on your own server without using them.


## Usage
### build package

|item|desc|
|:--|:--:|
|ENV|Set as {ENV}.env|
|APP_NAME|crawler-php|
|TWITTER_BEARER_TOKEN|your-twitter-application-token|
|GOOGLE_APPLICATION_CREDENTIALS|set your-service-account-path|
|APP_DIR|crawler-php|

```
export ENV=develop
export APP_NAME=app_name
export SLACK_URL=https://hooks.slack.com/services/xxx/yyy/zzz
export TWITTER_BEARER_TOKEN=YOUR_TWITTER_TOKEN
export GOOGLE_APPLICATION_CREDENTIALS=path/to/service-account-key-file
export APP_DIR=your_application_dir

docker build -t ${APP_NAME} -f Dockerfile . --build-arg DEPLOY_ENV=${ENV} --build-arg APP_DIR=${APP_DIR}
```

### local test run

```
docker container run \
  -p 80:80 \
  -e DEPLOY_ENV=${ENV} \
  -e SLACK_URL=${SLACK_URL} \
  -e TWITTER_BEARER_TOKEN=${TWITTER_BEARER_TOKEN} \
  -e GOOGLE_APPLICATION_CREDENTIALS=/var/key/key.json \
  -v $PWD/${GOOGLE_APPLICATION_CREDENTIALS}:/var/key/key.json \
  ${APP_name}

# if volumes mount copy to .env from {ENV}.env
docker container run \
  -p 80:80 \
  -e DEPLOY_ENV=${ENV} \
  -e SLACK_URL=${SLACK_URL} \
  -e TWITTER_BEARER_TOKEN=${TWITTER_BEARER_TOKEN} \
  -e GOOGLE_APPLICATION_CREDENTIALS=/var/key/key.json \
  -v $PWD/${APP_DIR}:/var/www/html/ \
  -v $PWD/${GOOGLE_APPLICATION_CREDENTIALS}:/var/key/key.json \
  -v $PWD/000-default.conf:/etc/apache2/sites-available/000-default.conf \
  -v $PWD/apache2.conf:/etc/apache2/apache2.conf \
  ${APP_name}
```

### push google cloud repository

```
export GCP_PROJECT_ID=your-project-id
export TAG=develop

docker tag ${APP_NAME}:latest asia.gcr.io/${GCP_PROJECT_ID}/${APP_NAME}:${TAG}
docker push asia.gcr.io/${GCP_PROJECT_ID}/${APP_NAME}:${TAG}
# or
docker push gcr.io/${GCP_PROJECT_ID}/${APP_NAME}:${TAG}
```
