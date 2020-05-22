<?php

namespace Bokt\Phpbb\Models;

use Bokt\Phpbb\Concerns\CompositePrimaryKey;
use Flarum\Database\AbstractModel;
use Flarum\Discussion\UserState;

/**
 * @property int $user_id
 * @property int $topic_id
 * @property int $mark_time
 * @property int $forum_id
 */
class TopicsTrack extends Model
{
    use CompositePrimaryKey;

    public $incrementing = false;
    protected $primaryKey = ['user_id', 'topic_id'];
    protected $table = 'topics_track';
    protected $dates = ['mark_time'];

    public function import(): ?AbstractModel
    {
        UserState::unguard();
        /** @var UserState $state */
        $state = UserState::firstOrNew([
            'discussion_id' => $this->topic_id,
            'user_id' => $this->user_id
        ]);
        UserState::reguard();

        if ($state->last_read_at && $state->last_read_at < $this->mark_time) {
            $state->last_read_at = $this->mark_time;
        }

        $state->load('discussion');

        if (!$state->last_read_post_number && $state->isDirty('last_read_at') && $state->discussion) {
            $state->last_read_post_number = optional($state->discussion
                ->posts()
                ->where('created_at', '<=', $state->last_read_at)
                ->orderBy('created_at', 'asc')
                ->first())->number;
        }

        return self::unsafeInsert(UserState::class, function () use ($state) {
            $state->save();

            return $state;
        });
    }

    public function setMarkTimeAttribute(int $value)
    {
        $this->attributes['mark_time'] = $value;
    }
}
