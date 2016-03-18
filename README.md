# Laravel Eloquent Model - JSON column support

[![Build Status](https://travis-ci.org/bluora/laravel-model-json-column.svg?branch=master)](https://travis-ci.org/bluora/laravel-model-json-column) [![StyleCI](https://styleci.io/repos/53236988/shield)](https://styleci.io/repos/53236988) [![Test Coverage](https://codeclimate.com/github/bluora/laravel-model-json-column/badges/coverage.svg)](https://codeclimate.com/github/bluora/laravel-model-json-column/coverage) [![Code Climate](https://codeclimate.com/github/bluora/laravel-model-json-column/badges/gpa.svg)](https://codeclimate.com/github/bluora/laravel-model-json-column)

Adds support for the JSON datatype column for models provided by the [Eloquent ORM](http://laravel.com/docs/eloquent).

## Installation

Require this package in your `composer.json` file:

`"bluora/laravel-model-json-column": "~1.0"`

Then run `composer update` to download the package to your vendor directory.

## Usage

### Basic

The feature is exposed through a trait that allows you to define columns that contain JSON data. When the model is created, it generates methods using the specified column names. You can then get and set the attributes directly.

```php
use ModelJsonColumn\JsonColumnTrait;

class User extends Model
{
    use JsonColumnTrait;

    protected $json_columns = [
        'settings'
    ];
}
```

The JSON column values can then be get or set via an object property:-
(the value can be an array or an object too!)

```php
$user->settings()->showProfilePicture;
```

### Defaults

You can define default values for a json attribute by using the `$json_defaults` property on the model.

You specify the attribute name and default value, if the name does not exist, it will be added at the creation of the object.

```
protected $json_defaults = [
    'settings' => ['showProfilePicture' => 0]
];
```

### Saving changes

When a save event has been called, the trait sets the original attribute value with the latest JSON encoded value.

If you have used defaults, you can stop these from being saved to the database by setting the option `no_saving_default_values` to true for the specific json column

```
protected $json_options = [
    'settings' => ['no_saving_default_values' => true]
];
```
