<?php

namespace Bokt\Phpbb\Models;

use Flarum\Database\AbstractModel;
use Illuminate\Database\Eloquent\Collection;

/**
 * @property int $role_id
 * @property string $role_name
 * @property string $role_description
 * @property string $role_type
 * @property string $role_order
 * @property Collection $options
 */
class AclRole extends Model
{
    protected $primaryKey = 'role_id';

    public function import(): ?AbstractModel
    {
        // ..
    }

    public function options()
    {
        return $this->hasMany(AclRolesData::class, 'role_id');
    }
}
