# Migrate PhpBB to Flarum

This extension allows migrating a phpbb forum to flarum.

Please note:

- duplicate email addresses get subaddressing added with the user id: `test@local.test` becomes `test+34@local.test`
- usernames are normalised to be only strings and numbers, the 3 minimum limit has bee reduced to 1
- this extension might need customisation for your specific phpbb forum, file an issue with questions please

## Installation

Use Bazaar or install using composer:

```bash
$ composer require bokt/flarum-phpbb-migrate
```

After that enable the extension in your admin area.

> Based on a migration from bokt.nl using PhpBB 3.x.

## Configuration

Inside your `config.php` create a new key `phpbb` with the database information for the PhpBB forum
you want to migrate, like so:

```php
return [
    'url' => '..',
    // .. other stuff
    'phpbb' => [
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'phpbb',
        'username'  => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix'    => '',
        'port'      => '3306',
        'strict'    => false,
    ]
];
```

## Use

Run the command.

```
php flarum phpbb:migrate
```

You should be able to rerun this command.
