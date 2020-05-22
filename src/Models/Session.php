<?php

namespace Bokt\Phpbb\Models;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;

/**
 * @property string $session_id
 * @property int $session_user_id
 * @property Carbon $session_last_visit
 * @property Carbon $session_start
 * @property Carbon $session_time
 * @property string $session_ip
 * @property string $session_browser
 * @property string $session_page
 * @property string $session_forwarded_for
 *
 * @property User $user
 */
class Session extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'session_id';

    protected $casts = [
        'session_last_visit' => 'datetime',
        'session_start' => 'datetime',
        'session_time' => 'datetime',
    ];

    public function import(): ?AbstractModel
    {
        // ..
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'session_user_id');
    }
}
