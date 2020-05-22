<?php

namespace Bokt\Phpbb\Models;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Formatter\Event\Parsing;
use Flarum\Formatter\Formatter;
use Flarum\Post\CommentPost;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * @property int $post_id
 * @property int $topic_id
 * @property int $forum_id
 * @property int $poster_id
 * @property int $icon_id
 * @property string $poster_ip
 * @property int $post_time
 * @property int $post_approved
 * @property string $post_username
 * @property string $post_subject
 * @property string $post_text
 * @property string $post_checksum
 * @property int $bbcode_uid
 */
class Post extends Model
{
    protected $primaryKey = 'post_id';

    public function import(): ?AbstractModel
    {
        /** @var CommentPost $post */
        $post = CommentPost::findOrNew($this->post_id);

        // Prevent overwriting of posts created inside Flarum.
        if ($post->exists && $this->postIsImported($post) === false) {
            if ($post->number === null) {
                $post->number = $this->getNumber($post);
                $post->save();
            }

            return null;
        }

        $post->id = $this->post_id;
        $post->discussion_id = $this->topic_id;
        $post->created_at = Carbon::createFromTimestamp($this->post_time);
        $post->ip_address = $this->poster_ip;
        $post->user_id = $this->poster_id;

        // Check soft deleted.
        $post->hidden_at = $this->post_approved <= 0 ? Carbon::now() : null;

        $post->number = $this->getNumber($post);

        $post->attributes['content'] = $this->formatContent($this->post_text, $post);

        CommentPost::query()->getConnection()->getSchemaBuilder()->disableForeignKeyConstraints();

        $post->save();

        CommentPost::query()->getConnection()->getSchemaBuilder()->enableForeignKeyConstraints();

        return $post;
    }

    protected function getNumber(CommentPost $post): int
    {
        return $this->number ?? CommentPost::query()
            ->where('discussion_id', $this->topic_id)
            ->where('created_at', '<=', $post->created_at)
            ->where('id', '!=', $post->id)
            ->count();
    }

    protected function formatContent(string $content, CommentPost $post)
    {
        // Take care of smilies in text.
        $content = str_replace('src="{SMILIES_PATH}', 'class="emoticon" src="/s', $content);
        if ($this->bbcode_uid) {
            // Remove the uuid phpbb adds in its bbcode.
            $content = str_replace(":{$this->bbcode_uid}", "", $content);
        }

        // Remove the video bbcode, this is handled by media embed natively.
        $content = str_replace(['[video]', '[/video]'], "", $content);

        // Replace the upload URL for uploaded images.
        $content = str_replace("{{IMG_UPLOAD_URL}}", '/img/', $content);

        // Replace links to topics.
        $content = preg_replace_callback('/viewtopic\.php\?(([a-z]+\=[^&]+(&amp;)?)*)t=(?<discussionId>[0-9]+)/', function ($matches) {
            return '/d/' . $matches['discussionId'];
        }, $content);

        $content = html_entity_decode($content);

        /** @var Formatter $formatter */
        $formatter = app(Formatter::class);

        // Parse imported php content for saving into Flarum.
        $content = $formatter->parse($content, $post);

        return $content;
    }
}
