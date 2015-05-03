Optimus id obfuscation
======================

[![Latest Stable Version](http://img.shields.io/github/release/jenssegers/optimus.svg)](https://packagist.org/packages/jenssegers/optimus) [![Build Status](http://img.shields.io/travis/jenssegers/optimus.svg)](https://travis-ci.org/jenssegers/optimus) [![Coverage Status](http://img.shields.io/coveralls/jenssegers/optimus.svg)](https://coveralls.io/r/jenssegers/optimus?branch=master)


With this library, you can obfuscate your internal id's to obfuscated integers based on Kunth's integer hash.

Installation
------------

Install using composer:

```
composer require jenssegers/optimus
```

Usage
-----

To get started you will need 3 things;

 - Large prime number lower than `2147483647`
 - The inverse prime so that `(PRIME * INVERSE) & MAXID == 1`
 - A large random integer lower than `2147483647`

You can calculate a prime number yourself, or pick one from this [list](http://primes.utm.edu/lists/small/millions/). Once you have selected a prime number, you can use the included console command to calculated the inverse prime that is used for the decoding process and generate a random integer:

```
> php vendor/bin/optimus spark YOUR_PRIME

Prime: 1580030173
Inverse: 59260789
Random: 1163945558
```

Using those numbers, you can start creating instances of `Optimus($prime, $inverted, $random)`:

```php
new Optimus(1580030173, 59260789, 1163945558);
```

## Encoding and decoding

To encode id's, use the `encode` method:

```php
$encoded = $optimus->encode(20); // 1535832388
```

To decode the resulting `1535832388` back to its original value, use the `decode` method:

```php
$original = $optimus->decode(1535832388); // 20
```
