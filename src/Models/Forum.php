<?php

namespace Bokt\Phpbb\Models;

use Flarum\Database\AbstractModel;
use Flarum\Tags\Tag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

/**
 * @property int    $forum_id
 * @property int    $parent_id
 * @property int    $left_id
 * @property string $forum_name
 * @property string $forum_desc
 * @property int    $forum_type
 * @property int    $forum_posts
 * @property int    $forum_topics
 * @property int    $forum_last_post_id
 * @property int    $forum_last_poster_id
 * @property int    $forum_last_post_time
 * @property string $forum_last_poster_name
 * @property bool   $display_on_index ; Whether to show the parent of the subforum.
 * @property Forum  $parent
 * @property Collection|AclGroup[] $aclGroups
 */
class Forum extends Model
{
    protected $primaryKey = 'forum_id';

    protected $casts = [
        'display_on_index' => 'boolean'
    ];

    public function import(): ?AbstractModel
    {
        return Tag::unguarded(function () {
            if ($this->isCategoryLabel()) {
                optional(Tag::find($this->forum_id))->delete();

                return null;
            }

            Tag::query()->getConnection()->getSchemaBuilder()->disableForeignKeyConstraints();

            preg_match('/(^\[(?<prefix>[^\]]+)\] )?(?<name>.*)$/i', $this->forum_name, $m);

            $slug = Str::slug($this->forum_name);

            if (isset($m['prefix']) && strlen($m['prefix']) > 0) {
                $slug = Str::slug($m['prefix']);
            } else if (isset($m['name']) && strlen($m['name']) > 0) {
                $slug = Str::slug($m['name']);
            }

            // Identify whether we need to increase the length of the tag slug with the parent slug appended.
            $appendParentSlug = false;
            if (Tag::where('slug', $slug)->when(Tag::find($this->forum_id), function ($query) {
                $query->where('id', '!=', $this->forum_id);
            })->exists()) {
                $appendParentSlug = true;
            }

            $parent = $this->parent;

            if ($parent && $parent->isCategoryLabel()) {
                $parent = $parent->parent;
            }

            /** @var Tag $tag */
            $tag = Tag::updateOrCreate([
                'id' => $this->forum_id
            ], [
                'name'        => $this->forum_name,
                'slug'        => $appendParentSlug ? Str::random(5) : $slug,
                'description' => $this->forum_desc,
                'parent_id'   => $parent ? $parent->forum_id : null,
            ]);

            $parent = $tag->parent ?? Tag::find($this->parent_id) ?? null;

            if ($appendParentSlug) {
                $tag->slug = $parent && $parent->slug ? $parent->slug . '-' . $slug : Str::slug($this->forum_name);
            }

            $tag->position = $this->left_id;

            $tag->save();

            Tag::query()->getConnection()->getSchemaBuilder()->enableForeignKeyConstraints();

            $this->aclGroups()->each(function (AclGroup $group) {
                $group->import();
            });

            return $tag;
        });
    }

    public function isCategoryLabel(): bool
    {
        return $this->parent_id > 0
            && $this->forum_type === 0
            && ! $this->display_on_index
            && $this->forum_last_poster_name === null
            && ! $this->parent->isCategoryLabel();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function aclGroups()
    {
        return $this->hasMany(AclGroup::class, 'forum_id')
            ->orderBy('auth_option_id');
    }
}
