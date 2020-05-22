<?php

namespace Bokt\Phpbb\Models;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;
use Flarum\Tags\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @property int $topic_id
 * @property int $forum_id
 * @property int $icon_id
 * @property int $topic_approved
 * @property int $topic_reported
 * @property string topic_title
 * @property int $topic_poster
 * @property int $topic_time
 * @property int $topic_views
 * @property int $topic_replies
 * @property int $topic_replies_real
 * @property int $topic_status
 * @property int $topic_type
 * @property int $topic_first_post_id
 * @property string $topic_first_poster_name
 * @property int $topic_last_post_id
 * @property string $topic_last_poster_name
 * @property int $topic_last_post_time
 * @property int $topic_moved_id
 * @property Collection|Post[] $posts
 */
class Topic extends Model
{
    protected $primaryKey = 'topic_id';

    public function posts()
    {
        return $this->hasMany(Post::class, 'topic_id');
    }

    public function import(): ?AbstractModel
    {
        /** @var Discussion $discussion */
        $discussion = Discussion::findOrNew($this->topic_id);

        // When we are handling an entirely new discussion, we will skip some
        // routine property/relation setter to optimize for speed. This is especially
        // required when seeding the posts as well.
        if ($discussion->exists) {
            $firstPostId = $this->posts()->selectRaw('MIN(post_id) as id')->first();
            $lastPostId = $this->posts()->selectRaw('MAX(post_id) as id')->first();

            $firstPost = $firstPostId ? $this->posts()->find($firstPostId->id) : optional();
            $lastPost = $lastPostId ? $this->posts()->find($lastPostId->id) : optional();

            $postCount = $this->posts()->count();

            $discussion->first_post_id = $firstPost->post_id;

            $discussion->last_post_id = $lastPost->post_id;
            $discussion->last_posted_user_id = $lastPost->poster_id;
            $discussion->last_posted_at = $lastPost ? Carbon::createFromTimestamp($lastPost->post_time) : null;
            $discussion->last_post_number = $postCount;

            $discussion->post_number_index = $postCount + 1;

            $discussion->participant_count = $this->posts()->groupBy('poster_id')->count();
        }

        $discussion->id = $this->topic_id;
        $discussion->title = html_entity_decode($this->topic_title);
        $discussion->created_at = Carbon::createFromTimestamp($this->topic_time);
        $discussion->user_id = $this->topic_poster;
        $discussion->slug = Str::slug($this->topic_title);
        $discussion->comment_count = $this->topic_replies_real;

        $discussion->hidden_at = $this->topic_approved !== 1 ? $discussion->created_at : null;

        $discussion->is_locked = $this->topic_status === 1;

        if ($discussion->isDirty()) {
            $discussion->save();
        }

        if ($this->forum_id > 0 && $tag = Tag::find($this->forum_id)) {
            $tags = [$tag->id];

            while($tag = $tag->parent) {
                $tags[] = $tag->id;
            }

            $discussion->tags()->sync($tags);
        }

        return $discussion;
    }
}
