Gaufrette Fast Extras
============

**Gaufrette Fast Exras** is a PHP library providing high performance extras for 
[Gaufrette](https://github.com/KnpLabs/Gaufrette).


Installation
============

The recommended way to install this library is through composer.

Just create a `composer.json` file for your project:

```json
{
    "require": {
        "wormling/gaufrette-fast-extras": "~1.0"
    }
}
```

And run these two commands to install it:

```bash
$ wget http://getcomposer.org/composer.phar
$ php composer.phar install
```


Now you can add the autoloader, and you will have access to the library:

```php
require 'vendor/autoload.php';
```

### FastCache a slow filesystem - Optimistic version of (https://github.com/KnpLabs/Gaufrette/blob/master/README.markdown#cache-a-slow-filesystem)

The primary difference so far is that the source file is not verified for existence nor is the timestamp checked until the ttl is expired.  This
was necessary in the case of remote sources such as S3 as the request overhead for small files is often similar to the actual retrieval thus making 
the cache not significantly faster than just plain S3.

License
=======

This library is released under the MIT license. See the bundled LICENSE file
for details.


Funded by Acumantra Solutions, Inc.
