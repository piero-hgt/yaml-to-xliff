#!/bin/bash
composer install --no-dev --optimize-autoloader
box build
chmod a+x yamlConvert2Xliff.phar
mv yamlConvert2Xliff.phar yamlConvert2Xliff
