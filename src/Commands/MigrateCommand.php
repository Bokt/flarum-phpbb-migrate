<?php

namespace Bokt\Phpbb\Commands;

use Bokt\Phpbb\Pipeline;
use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    protected $signature = 'phpbb:migrate';
    protected $description = 'Migrate phpbb to flarum.';

    public function handle(Pipeline $pipeline)
    {
        $pipeline->setOutputInterface($this->output);

        $pipeline->run();
    }
}
