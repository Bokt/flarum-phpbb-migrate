<?php

namespace Bokt\Phpbb;

use Flarum\Foundation\AbstractServiceProvider;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Support\Arr;

class Provider extends AbstractServiceProvider
{
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
