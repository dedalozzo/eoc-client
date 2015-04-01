[![Latest Stable Version](https://poser.pugx.org/3f/eoc-client/v/stable.png)](https://packagist.org/packages/3f/eoc-client)
[![Latest Unstable Version](https://poser.pugx.org/3f/eoc-client/v/unstable.png)](https://packagist.org/packages/3f/eoc-client)
[![License](https://poser.pugx.org/3f/eoc-client/license.svg)](https://packagist.org/packages/3f/eoc-client)
[![Total Downloads](https://poser.pugx.org/3f/eoc-client/downloads.png)](https://packagist.org/packages/3f/eoc-client)


Elephant on Couch Client
========================
EoC Client is the most advanced CouchDB Client Library.


Composer Installation
---------------------

To install EoC Client, you first need to install [Composer](http://getcomposer.org/), a Package Manager for
PHP, following those few [steps](http://getcomposer.org/doc/00-intro.md#installation-nix):

```sh
curl -s https://getcomposer.org/installer | php
```

You can run this command to easily access composer from anywhere on your system:

```sh
sudo mv composer.phar /usr/local/bin/composer
```


EoC Client Installation
-----------------------
Once you have installed Composer, it's easy install EoC Client.

1. Edit your `composer.json` file, adding EoC Client to the require section:
```sh
{
    "require": {
        "3f/eoc-client": "dev-master"
    },
}
```
2. Run the following command in your project root dir:
```sh
composer update
```


Documentation
-------------
The documentation can be generated using [Doxygen](http://doxygen.org). A `Doxyfile` is provided for your convenience.


Requirements
------------
- PHP 5.4.0 or above.


Authors
-------
Filippo F. Fadda - <filippo.fadda@programmazione.it> - <http://www.linkedin.com/in/filippofadda>


License
-------
Lint is licensed under the Apache License, Version 2.0 - see the LICENSE file for details.