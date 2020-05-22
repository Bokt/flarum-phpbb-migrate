# Migrate PhpBB to Flarum

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

