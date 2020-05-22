<?php

namespace Bokt\Phpbb\Models;

use Flarum\Database\AbstractModel;

/**
 * @property int $auth_option_id
 * @property string $auth_option
 * @property bool $is_global
 * @property bool $is_local
 * @property bool $is_founder
 */
class AclOption extends Model
{
    protected $primaryKey = 'auth_option_id';

    protected $casts = [
        'is_global' => 'boolean',
        'is_local' => 'boolean',
        'is_found' => 'boolean',
    ];

    public function import(): ?AbstractModel
    {
        // ..
    }
}
