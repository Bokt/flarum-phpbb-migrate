# Laravel Horizon for Flarum

This Flarum extension adds all functionality of Horizon to Flarum,
including the dashboard.

Before you start make sure to read the full documentation about
[Horizon](https://laravel.com/docs/5.7/horizon). Horizon (currently)
only works with a redis [queue](https://laravel.com/docs/5.7/queues).

By default this extension will set up a default queue connection called
`horizon` using redis. You can override the full horizon config using
an extender in your local `extend.php` in the root of your flarum
installation:

```php
<?php

return [
    (new Bokt\Horizon\Extend\Horizon)->config(
        './your-horizon-config.php'
    )
];
```

I personally use the `env()` helper in configuration files and store
these files under the `config/` folder.

Once you installed `bokt/flarum-horizon` go into your admin area, enable
the extension so that the js and css files for the extension can be published.
Once you've done that, visit `/admin/horizon/` to see the full dashboard.
Please note that the admin dashboard requires you to be an admin in Flarum.