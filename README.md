Optimus id transformation
=========================

[![Latest Stable Version](http://img.shields.io/github/release/jenssegers/optimus.svg)](https://packagist.org/packages/jenssegers/optimus) [![Build Status](http://img.shields.io/travis/jenssegers/optimus.svg)](https://travis-ci.org/jenssegers/optimus) [![Coverage Status](http://img.shields.io/coveralls/jenssegers/optimus.svg)](https://coveralls.io/r/jenssegers/optimus?branch=master)


With this library, you can transform your internal id's to obfuscated integers based on Knuth's integer hash. It is similar to Hashids, but will generate integers instead of random strings. It is also super fast.

<p align="center">
<img src="http://jenssegers.be/uploads/images/optimus.png">
</p>

Installation
------------

Install using composer:

```
composer require jenssegers/optimus
```

For 32 bit systems it is required to install the [GMP extension](http://php.net/manual/en/book.gmp.php). For debian/ubuntu you can install the extension with one of these commands:

```
apt-get install php5-gmp
apt-get install php7.0-gmp
apt-get install php7.1-gmp
```

Usage
-----

To get started you will need 3 things;

 - Large prime number lower than `2147483647`
 - The inverse prime so that `(PRIME * INVERSE) & MAXID == 1`
 - A large random integer lower than `2147483647`

Luckily for you, I have included a console command that can do all of this for you. To get started, just run the following command:

```
> php vendor/bin/optimus spark

Prime: 2123809381
Inverse: 1885413229
Random: 146808189
```

If you prefer to choose your own prime number (from [this list](http://primes.utm.edu/lists/small/millions/) for example), you can pass it to the command to calculate the remaining numbers:

```
> php vendor/bin/optimus spark 1580030173

Prime: 1580030173
Inverse: 59260789
Random: 1163945558
```

Using those numbers, you can start creating instances of `Optimus($prime, $inverted, $random)`:

```php
use Jenssegers\Optimus\Optimus;

new Optimus(1580030173, 59260789, 1163945558);
```

**NOTE**: Make sure that you are using the same constructor values throughout your entire application!

## Encoding and decoding

To encode id's, use the `encode` method:

```php
$encoded = $optimus->encode(20); // 1535832388
```

To decode the resulting `1535832388` back to its original value, use the `decode` method:

```php
$original = $optimus->decode(1535832388); // 20
```

## Framework Integrations

### Laravel

This is an example service provider which registers a shared Optimus instance for your entire application:

```php
<?php

namespace App\Providers;

use Jenssegers\Optimus\Optimus;
use Illuminate\Support\ServiceProvider;

class OptimusServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Optimus::class, function ($app) {
            return new Optimus(1580030173, 59260789, 1163945558);
        });
    }
}
```

Once you have created the service provider, add it to the providers array in your `config/app.php` configuration file:

```
App\Providers\OptimusServiceProvider::class,
```


Laravel's automatic injection will pass this instance where needed. Example controller:

```php
<?php

namespace App\Http\Controllers;

use Jenssegers\Optimus\Optimus;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
    public function show($id, Optimus $optimus)
    {
        $id = $optimus->decode($id));
    }
}
```

More information: https://laravel.com/docs/5.3/container#resolving

**Third-party integrations**

An integration with Laravel is provided by the [propaganistas/laravel-fakeid](https://packagist.org/packages/propaganistas/laravel-fakeid) package.

### Silex

An integration with Silex 2 is provided by the [jaam/silex-optimus-provider](https://packagist.org/packages/jaam/silex-optimus-provider) package.

See the repository for more information: https://github.com/letsjaam/silex-optimus-provider
