<?php

namespace Bokt\Phpbb\Models;

use Bokt\Phpbb\Concerns\CompositePrimaryKey;
use App\Sync\Contracts\BatchImportable;
use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Tags\TagState;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int $user_id
 * @property int $mark_time
 * @property int $forum_id
 */
class ForumsTrack extends Model implements BatchImportable
{
    use CompositePrimaryKey;

    public $incrementing = false;
    protected $primaryKey = ['user_id', 'forum_id'];
    protected $table = 'forums_track';
    protected $dates = ['mark_time'];

    public function import(): ?AbstractModel
    {
        /** @var TagState $state */
        $state = TagState::firstOrNew([
            'tag_id' => $this->forum_id,
            'user_id' => $this->user_id
        ]);

        $state->marked_as_read_at = $state->marked_as_read_at ?? $this->mark_time;

        return self::unsafeInsert(TagState::class, function () use ($state) {
            $state->save();

            return $state;
        });
    }

    public static function batchImport()
    {
        self::query()
            ->orderBy('user_id')
            ->chunk(500, function (Collection $collection) {
                $insert = $collection->map(function ($phpbb) {
                    return [
                        'tag_id' => $phpbb->forum_id,
                        'user_id' => $phpbb->user_id,
                        'marked_as_read_at' => $phpbb->mark_time,
                    ];
                });

                self::unsafeInsert(TagState::class, function () use ($insert) {
                    TagState::query()->insert($insert->toArray());
                });
            });
    }
}
