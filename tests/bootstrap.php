<?php

if (!($loader = @include __DIR__ . '/../vendor/autoload.php')) {
        exit(<<<EOT
You need to install the project dependencies using Composer:
$ wget http://getcomposer.org/composer.phar
OR
$ curl -s https://getcomposer.org/installer | php
$ php composer.phar install --dev
$ phpunit
EOT
            );
}

$baseDir = dirname(__DIR__);

$loader = require __DIR__.'/../vendor/autoload.php';
$loader->add('GaufretteFastExtras', array($baseDir.'/src/', $baseDir.'/tests/'));
$loader->register();


