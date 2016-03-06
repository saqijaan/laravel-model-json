# Model JSON Column

Model JSON Column adds support for the JSON datatype column in a model provided by the [Eloquent ORM](http://laravel.com/docs/eloquent). 

## Installation

Require this package in your `composer.json` file:

`"bluora/model-json-column": "dev-master"`

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

The JSON column values can then be via an object property:-

```php
$user->settings()->showProfilePicture;
```

### Defaults

You an define default values for options $json_defaults_* propterty (if the column is `settings`, then $json_defaults_settings).

You specify the attribute and in an array, specify the default value, the value type, and in the 3rd item and any other type specific information (like ['', 'VARCHAR', 255]).

```
protected $json_defaults_settings = [
    'showProfilePicture' => [0, 'BOOLEAN']
];
```

### Saving changes

When a save event has been called, the trait sets the original attribute value with the latest JSON encoded value.

