# Generate data use php template

## Quick start

```bash
composer install
cd examples
../bin/gen owner
../bin/gen owner -r 10 -o result/owner.json
../bin/gen pet_type -r inf -o result/pet_type.json
../bin/gen pet -r 10 -o result/pet.json
../bin/gen visit -r 10 -o result/visit.json
```

## Template

Template is a simple php file that return an array :

```php
<?php

return [
    'id' => ['incrementId'],
    'first_name' => ['firstName'],
    'last_name' => ['lastName'],
    'city' => ['city'],
    'telephone' => ['phoneNumber']
];
```

The template is equal as:

```php

$faker = \Faker\Factory::create();

return [
    'id' => $faker->incrementId(),
    'first_name' => $faker->firstName(),
    'last_name' => $faker->lastName(),
    'city' => $faker->city(),
    'telephone' => $faker->phoneNumber()
];
```

Note all key starts with `_` will be discard when output. You can use key starts with `_`
to store temporal data to facilitate data generation.

Template value type can be one of:

### array

When template is an array, it interpret as `['method', 'arg1', 'arg2', ...]`。

Same as :

```php
call_user_func_array([$faker, 'method'], ['arg1', 'arg2', ...]);
```

### string

When template is an string, first it split the string using `.`.

if first part of the string is an entry of current result, then get the entry of result. 

if first part of the string is not present in current result, then use `$faker->pickup($name)` random pickup one from the dataset.

The rest part of the string use as the index for the entry.

For example: `pet_type` will random choose one from `pet_type.json`.

### iterator

When template is an iterator, it will get the next value of the iterator.

For example:

```php
<?php

return [
    'type' => new ArrayIterator(['a', 'b'])
];
```

This will generator two rows:

```json
[
  {"type":"a"},
  {"type":"b"}
]
```

Note the `\Generator` is also an iterator, so this is same as previous one:

```php
<?php

return [
    'type' => function() {
        foreach (['a', 'b'] as $type) {
            yield $type;
        }
    }
];
```

### closure

When the template is an closure, the call result of the closure will be used.
if the call result is an iterator, the iterator value will use instead.

For example:

```php
<?php

return [
    'type' => function($faker) {
        return ['a', 'b'];
    }
];
```

The will generate :
```json
[
  {"type": ["a", "b"]}
]
```

But:

```php
<?php

return [
    'type' => function($faker) {
        return new ArrayIterator(['a', 'b']);
    }
];
```

It generates: 
```json
[
  {"type":"a"},
  {"type":"b"}
]
```

## Config

You can create a file `.faker-config` in current directory or home directory. The config file is in json format which contains:
 
- locale the locale for faker generator
- template the template directory 
- dataset the data set directory
- options additional command line options

## Generators


```php
incrementId($name = 'default', $start = 1)   // 1, 2, 3, ...
pickup($dataSetName)
dataset($dataSetName)
```

## Tips

- use `$faker->optional()` to create nullable value
- use `$faker->unique()` to create unique value
