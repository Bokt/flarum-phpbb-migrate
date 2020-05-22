<?php

namespace Bokt\Phpbb\Models;

use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;
use Flarum\User\User as FlarumUser;

/**
 * Stipjes = follow state.
 *
 * @property int $user_id
 * @property int $topic_id
 * @property int $topic_posted
 */
class TopicsPosted extends Model
{
    protected $table = 'topics_posted';

    public static $translate = [
        1 => 'follow',
        0 => null,
        -1 => 'ignore'
    ];

    public function import(): ?AbstractModel
    {
        /** @var Discussion $discussion */
        $discussion = Discussion::findOrFail($this->topic_id);
        $state = $discussion->stateFor(FlarumUser::findOrFail($this->user_id));

        $state->subscription = self::$translate[$this->topic_posted];

        $state->save();

        return $state;
    }
}
