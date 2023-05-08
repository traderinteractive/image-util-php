# image-util-php

[![Build Status](https://travis-ci.org/traderinteractive/image-util-php.svg?branch=master)](https://travis-ci.org/traderinteractive/image-util-php)
[![Code Quality](https://scrutinizer-ci.com/g/traderinteractive/image-util-php/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/traderinteractive/image-util-php/?branch=master)

[![Latest Stable Version](https://poser.pugx.org/traderinteractive/image-util/v/stable)](https://packagist.org/packages/traderinteractive/image-util)
[![Latest Unstable Version](https://poser.pugx.org/traderinteractive/image-util/v/unstable)](https://packagist.org/packages/traderinteractive/image-util)
[![License](https://poser.pugx.org/traderinteractive/image-util/license)](https://packagist.org/packages/traderinteractive/image-util)

[![Total Downloads](https://poser.pugx.org/traderinteractive/image-util/downloads)](https://packagist.org/packages/traderinteractive/image-util)
[![Monthly Downloads](https://poser.pugx.org/traderinteractive/image-util/d/monthly)](https://packagist.org/packages/traderinteractive/image-util)
[![Daily Downloads](https://poser.pugx.org/traderinteractive/image-util/d/daily)](https://packagist.org/packages/traderinteractive/image-util)

General Image Utility Library

## Requirements

image-util-php requires PHP 7.0 (or later).

## Composer
To add the library as a local, per-project dependency use [Composer](http://getcomposer.org)! Simply add a dependency on
`traderinteractive/image-util` to your project's `composer.json` file such as:

```sh
composer require traderinteractive/image-util
```

## Documentation

Found in the [source](src) itself, take a look!

## Contact

Developers may be contacted at:

 * [Pull Requests](https://github.com/traderinteractive/image-util-php/pulls)
 * [Issues](https://github.com/traderinteractive/image-util-php/issues)

## Project Build

With a checkout of the code get [Composer](http://getcomposer.org) in your PATH and run:

```sh
./vendor/bin/phpunit
./vendor/bin/phpcs
```

There is also a [docker](http://www.docker.com/)-based [fig](http://www.fig.sh/) configuration that will execute the build inside a docker container.  This is an easy way to build the application:

```sh
fig run build
```

For more information on our build process, read through out our [Contribution Guidelines](.github/CONTRIBUTING.md).
