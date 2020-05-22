<?php

namespace Bokt\Phpbb\Concerns;

use App\Sync\phpBB\Group;
use Flarum\Group\Group as Flarum;

trait ReplacesGenericGroups
{
    public $map = [
        'GUESTS'            => Flarum::GUEST_ID,
        'ADMINISTRATORS'    => Flarum::ADMINISTRATOR_ID,
        'GLOBAL_MODERATORS' => Flarum::MODERATOR_ID,
        'REGISTERED'        => Flarum::MEMBER_ID
    ];

    protected function getGroup(Group $group): Flarum
    {
        /** @var Flarum $in */
        $in = Flarum::find($group->group_id) ?? new Flarum;
        $in->id = $group->group_id;

        if ($group->group_type === 3  && in_array($group->group_name, array_keys($this->map))) {
            $in = Flarum::find($this->map[$group->group_name]) ?? $in;
        }

        return $in;
    }
}
