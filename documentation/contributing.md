# Contributing

In order to run tests locally please make sure you have [docker](https://www.docker.com/) up and running.
You also need [PHP 8.1](https://www.php.net/) and [composer](https://getcomposer.org/) to be available from your CLI.
Even that we are supporting 3 PHP versions at time, we are using the lowest supported one for development, currently it's PHP 8.1.

For the code coverage, please install [pcov](https://pecl.php.net/package/pcov).

### Before you change anything

Please make sure that you are aware of our [Architecture Decision Records](/documentation/adrs.md).
It's mandatory to follow all of them without any exceptions unless explicitly overridden by a new ADR.

### Prepare Project:

```shell
cp compose.yml.dist compose.yml
composer install 
docker compose up -d
```

### Run Test Suite

```shell
composer test
```

### Run Static Analyze

```shell
composer static:analyze
```

### Fixing Coding Standards

Before committing your code, please make sure that your code is following our coding standards.

```shell
composer cs:php:fix
```

### Test everything

This command will execute exactly the same tests as we run at GitHub Actions before PR can get merged.
If it passes locally, you are good to open pull request.

```shell
composer build 
```

## Building PHAR

```shell
composer build:phar
./build/flow.phar --version
```

## Building Docker Image

In order to build docker image and load it to local registry please use: 

```shell
docker buildx build -t flow-php/flow:latest . --progress=plain  --load
```

Usage:

```shell
docker run -v $(pwd):/flow-workspace -it flow-php/flow:latest --version
```
