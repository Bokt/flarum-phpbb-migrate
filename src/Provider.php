<?php

namespace Bokt\Phpbb;

use Flarum\Foundation\AbstractServiceProvider;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;

class Provider extends AbstractServiceProvider
{
    public function register()
    {
        // Overrides Flarum native validator to allow usernames with length 1 and up.
        $this->app->bind(\Flarum\User\UserValidator::class, function ($app) {
            return $app->make(Validators\UserValidator::class);
        });
    }

    public function boot()
    {
        $this->app->extend(Manager::class, function (Manager $manager) {
            $config = $this->app->make('flarum.config') ?? [];

            if ($phpbb = Arr::get($config, 'phpbb')) {
                $manager->addConnection($phpbb, 'phpbb');
            }

            return $manager;
        });
    }
}
