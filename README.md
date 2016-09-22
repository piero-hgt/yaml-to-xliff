# YAML2XLIFF converter

This is a repository with a simple Symfony console application to convert 'yaml' translation files into 'xliff' format.

## Develop:

### Dependencies
* "php": "^5.3.9|^7.0"
* "ext-xmlwriter": "*"
* "symfony/console": "^2.8|^3.0"
* "symfony/dependency-injection": "^2.8|^3.0"
* "symfony/config": "^2.8|^3.0"
* "symfony/yaml": "^2.8|^3.0"


Use [Composer](https://getcomposer.org/) to install all dependencies needed for this project 

```bash
composer install

```

### Convert
You can then run the tool by simply executing the `bin/yamlConvert2Xliff` script.


## Build

In order to build an executable PHAR file, you need
[Composer](https://getcomposer.org) and [Box](http://box-project.github.io/box2/).

```bash
composer install --no-dev --optimize-autoloader
box build
chmod a+x yamlConvert2Xliff.phar
```

## Usage

```bash
Usage:
  command [options] [arguments]

Options:
  -h, --help            Display this help message
  -q, --quiet           Do not output any message
  -V, --version         Display this application version
  -v|vv|vvv, --verbose  Increase verbosity of messages: 1 normal output, 2 more verbose output and 3 for debug
  
Available commands:
  convert  Dumps all translations in als XLIFF to stdout.
  help     Displays help for a command
  info     Displays statistics about the translations stored in the database.
  list     Lists commands
```

## Note
Sometimes you have to uncomment auto_prepend_file option in your php.ini
