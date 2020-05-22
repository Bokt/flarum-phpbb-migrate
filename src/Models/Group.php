<?php

namespace Bokt\Phpbb\Models;

use Bokt\Phpbb\Concerns\ReplacesGenericGroups;
use Flarum\Database\AbstractModel;
use Flarum\Group\Group as FlarumGroup;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

/**
 * @property int               $group_id
 * @property int               $group_type
 * @property string            $group_name
 * @property string            $group_colour
 * @property int               $role_order
 * @property Collection|User[] $users
 */
class Group extends Model
{
    use ReplacesGenericGroups;

    protected $primaryKey = 'group_id';

    public function import(): ?AbstractModel
    {
        if ($this->group_id <= 4) {
            throw new InvalidArgumentException("Cannot sync group which is reserved in Flarum (admin, guest, member, mod).");
        }

        $group = $this->getGroup($this);

        if ($group->id > FlarumGroup::MODERATOR_ID && $this->users()->count() === 0) {
            $group->delete();

            return null;
        } else {
            $group->name_singular = $this->group_name;
            $group->name_plural   = $group->name_singular;
            $group->color         = empty($this->group_colour) ? '' : "#" . $this->group_colour;
        }

        $group->save();

        FlarumGroup::query()->getConnection()->getSchemaBuilder()->disableForeignKeyConstraints();

        // User assignment.
        if ($group->id !== FlarumGroup::MEMBER_ID && $group->id !== FlarumGroup::GUEST_ID) {
            $group->users()->sync($this->users()->pluck('user_id')->toArray());
        }

        FlarumGroup::query()->getConnection()->getSchemaBuilder()->enableForeignKeyConstraints();

        // Permissions, admin group needs no specific permission or it will nullify their access.
        if($group->id === FlarumGroup::GUEST_ID) {
            $group->permissions()->delete();
            $group->permissions()->insert(['group_id' => $group->id, 'permission' => 'viewDiscussions']);
        }

        if ($group->id === FlarumGroup::ADMINISTRATOR_ID) {
            $group->permissions()->delete();
        }

        // Clean up any duplicates.
        FlarumGroup::query()
            ->where('name_singular', $group->name_singular)
            ->where('id', '!=', $group->id)
            ->delete();

        return $group;
    }

    public function users()
    {
        return $this->hasMany(User::class, 'group_id');
    }

    public function aclGroups()
    {
        return $this->hasMany(AclGroup::class, 'group_id', 'group_id')
            ->orderBy('auth_option_id');
    }
}
