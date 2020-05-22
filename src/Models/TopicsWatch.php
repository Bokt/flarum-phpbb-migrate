<?php

namespace Bokt\Phpbb\Models;

use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;
use Flarum\User\User as FlarumUser;

/**
 * Abonnement.
 *
 * @property int $user_id
 * @property int $topic_id
 * @property int $notify_status 0 indicates no new messages, 1 means informed about new replies
 */
class TopicsWatch extends Model
{
    protected $table = 'topics_watch';
    public $incrementing = false;
    protected $primaryKey = ['user_id', 'topic_id'];

    public function import(): ?AbstractModel
    {
        /** @var Discussion $discussion */
        $discussion = Discussion::findOrFail($this->topic_id);
        $state = $discussion->stateFor(FlarumUser::findOrFail($this->user_id));

        $state->subscription = 'follow';

        $state->save();
    }
}
