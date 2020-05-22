<?php

namespace Bokt\Phpbb;

use Flarum\Extend\Console;

return [
    new Extend\Provider(Provider::class),
    (new Console())
        ->command(Commands\MigrateCommand::class)
];
