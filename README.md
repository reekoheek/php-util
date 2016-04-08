# php-util

PHP Common Utility Library

## Collection

Collection is an object that wrap associative array, and work like an associative array.

Collection is json serializer aware. See, [JsonKit](https://github.com/reekoheek/php-jsonkit).

Example
```php
use ROH\Util\Collection;

// instantiate empty collection
$collection = new Collection();

// instantiate filled collection
$filledCollection = new Collection([
    'foo' => 'bar'
]);

// define attribute
$collection['subCollection'] = $filledCollection;

// get foo of sub collection attribute from parent collection attribute
echo $collection['subCollection']['foo'];

```

## Composition

Compose array of callable / function to be called later as sequence of function call.

## Inflector

Inflection library

## Injector

Simple dependency injector

## Options

Compose options from array, or configuration files

## StringFormatter

Format string from string template