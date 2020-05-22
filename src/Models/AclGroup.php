<?php

namespace Bokt\Phpbb\Models;

use Bokt\Phpbb\Concerns\ReplacesGenericGroups;
use Flarum\Database\AbstractModel;
use Flarum\Group\Group as FlarumGroup;
use Flarum\Group\Permission;
use Flarum\Tags\Tag;
use Illuminate\Support\Arr;

/**
 * @property int       $group_id
 * @property int       $forum_id
 * @property int       $auth_option_id
 * @property int       $auth_setting
 * @property int       $auth_role_id
 * @property AclOption $option
 * @property AclRole   $role
 * @property Forum     $forum
 * @property Group     $group
 */
class AclGroup extends Model
{
    use ReplacesGenericGroups;

    protected $primaryKey = null;
    public $incrementing = false;

    protected static $mapping = [
        'f_read'  => 'viewDiscussions',
        'f_post'  => ['startDiscussion', 'discussion.likePosts', 'discussion.flagPosts'],
        'f_reply' => 'discussion.reply',
        'm_delete' => 'discussion.hide',
        'm_edit' => ['discussion.editPosts', 'discussion.rename', 'discussion.viewFlags'],
        'm_info' => 'discussion.viewIpsPosts',
        'm_lock' => 'discussion.lock',
        'm_move' => 'discussion.tag',
        'm_split' => 'discussion.split',
        'm_merge' => 'discussion.merge',
        'a_user' => 'user.edit'
    ];

    public function import(): ?AbstractModel
    {
        $restricted = true;
        $group = $this->getGroup($this->group);

        if (! $group->exists || $group->id === 1) return null;

        $this->group_id = $group->id;

        if ($this->auth_role_id > 0) {
            $options = $this->role->options;
        } else {
            $options = collect();
            $options->push((object) Arr::only($this->toArray(), ['auth_option_id', 'auth_setting']));
        }

        foreach ($options as $option) {
            if ($option->auth_setting === 1) {
                /** @var AclOption $aclOption */
                $aclOption = AclOption::find($option->auth_option_id);

                $this->importAuthOptionForForum($aclOption->auth_option, $this->forum_id);

                $restricted = !in_array(
                    $this->group_id,
                    [
                        FlarumGroup::GUEST_ID,
                        FlarumGroup::MEMBER_ID
                    ]
                );
            }
        }

        Tag::query()
            ->where('id', $this->forum_id)
            ->where('is_restricted', !$restricted)
            ->update(['is_restricted' => $restricted]);

        return null;
    }

    protected function importAuthOptionForForum(string $auth_option, int $forum_id)
    {
        $mapped = (array)Arr::get(static::$mapping, $auth_option, []);

        Permission::unguard();
        foreach ($mapped as $permission) {
            Permission::query()->firstOrCreate(
                [
                    'group_id'   => $this->group_id,
                    'permission' => "tag{$forum_id}.{$permission}"
                ],
                [
                    'group_id'   => $this->group_id,
                    'permission' => "tag{$forum_id}.{$permission}"
                ]
            );
        }

        Permission::reguard();
    }

    public function option()
    {
        return $this->belongsTo(AclOption::class, 'auth_option_id', 'auth_option_id');
    }

    public function role()
    {
        return $this->belongsTo(AclRole::class, 'auth_role_id');
    }

    public function forum()
    {
        return $this->belongsTo(Forum::class, 'forum_id');
    }

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id');
    }
}
