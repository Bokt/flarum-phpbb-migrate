<?php

namespace Bokt\Phpbb\Models;

use Bokt\Phpbb\Concerns\MassInserting;
use Flarum\Database\AbstractModel;

abstract class Model extends AbstractModel
{
    use MassInserting;

    public function getConnectionName()
    {
        return 'phpbb';
    }

    abstract public function import(): ?AbstractModel;
}
