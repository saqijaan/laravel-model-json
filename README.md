```
    __                                __    _______ ____  _   __
   / /   ____ __________ __   _____  / /   / / ___// __ \/ | / /
  / /   / __ `/ ___/ __ `/ | / / _ \/ /_  / /\__ \/ / / /  |/ / 
 / /___/ /_/ / /  / /_/ /| |/ /  __/ / /_/ /___/ / /_/ / /|  /  
/_____/\__,_/_/   \__,_/ |___/\___/_/\____//____/\____/_/ |_/   
                                                                
```

Adds support for the JSON datatype column for models via an object based interface.

[![Latest Stable Version](https://poser.pugx.org/hnhdigital-os/laravel-model-json/v/stable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-json) [![Total Downloads](https://poser.pugx.org/hnhdigital-os/laravel-model-json/downloads.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-json) [![Latest Unstable Version](https://poser.pugx.org/hnhdigital-os/laravel-model-json/v/unstable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-json) [![Built for Laravel](https://img.shields.io/badge/Built_for-Laravel-green.svg)](https://laravel.com/) [![License](https://poser.pugx.org/hnhdigital-os/laravel-model-json/license.svg)](https://packagist.org/packages/hnhdigital-os/laravel-model-json)

[![Build Status](https://travis-ci.org/hnhdigital-os/laravel-model-json.svg?branch=master)](https://travis-ci.org/hnhdigital-os/laravel-model-json) [![StyleCI](https://styleci.io/repos/53236988/shield)](https://styleci.io/repos/53236988) [![Test Coverage](https://codeclimate.com/github/hnhdigital-os/laravel-model-json/badges/coverage.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-json/coverage) [![Issue Count](https://codeclimate.com/github/hnhdigital-os/laravel-model-json/badges/issue_count.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-json) [![Code Climate](https://codeclimate.com/github/hnhdigital-os/laravel-model-json/badges/gpa.svg)](https://codeclimate.com/github/hnhdigital-os/laravel-model-json)

This package has been developed by H&H|Digital, an Australian botique developer. Visit us at [hnh.digital](http://hnh.digital).

## Install

`$ composer require hnhdigital-os/laravel-model-json ~1.0`

## Usage

### Basic

The feature is exposed through a trait that allows you to define columns that contain JSON data. When the model is created, it generates methods using the specified column names. You can then get and set the attributes directly.

```php
use Bluora\LaravelModelJson\JsonColumnTrait;

class User extends Model
{
    use JsonColumnTrait;

    protected $json_columns = [
        'settings'
    ];
}
```

The JSON column values can then be retrieved or set via an object property.

Let's say we have an array of data stored in the `settings` JSON column:

```php
['showProfilePicture' => true, 'options' => ['option1' => 'red']];
```

Getting these values is as simple as:
```php
echo $user->settings()->showProfilePicture."\n";
echo $user->settings()->options['option1'];
```

Would output:
```
1
red
```

You can update any variable or add a new one:
```php
$user->settings()->options['option2'] = 1;
$user->save();
```

And would update the JSON object with the following array:
```php
['showProfilePicture' => true, 'options' => ['option1' => 'red', 'option2' => 1]];
```

Calling `getDirty` with `true` will provide changes using dot notation.

```php
print_r($user->getDirty(true));
```

Would output:
```
array(
    'settings' => "{"showProfilePicture":true,"options":{"option1":"red","option2":1}}",
    'settings.options' => array('option2' => 1)
)
```

### NOTE

If you use `findOrNew`, `firstOrNew`, `firstOrCreate`, or the `updateOrCreate` method, you should run the `inspectJson` method before using any JSON columns as the `newFromBuilder` method (which we override) is not called on new model objects.

```php
$model = Model::firstOrNew(['name' => 'example']);
$model->inspectJson();
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

## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-git-info/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/therocis)
* [All Contributors](https://github.com/hnhdigital-os/laravel-git-info/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-git-info/blob/master/LICENSE) for more information.
