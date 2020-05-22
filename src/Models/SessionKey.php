<?php

namespace Bokt\Phpbb\Models;

use Flarum\Database\AbstractModel;

/**
 * @property string $key_id
 * @property int $user_id
 *
 * @property User $user
 */
class SessionKey extends Model
{
    protected $primaryKey = 'key_id';

    public function import(): ?AbstractModel
    {
        // ..
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
