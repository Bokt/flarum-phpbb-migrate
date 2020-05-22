<?php

namespace Bokt\Phpbb\Models;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Group\Group;
use Flarum\User\User as Flarum;
use Flarum\User\UserValidator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

/**
 * @property int            $user_id
 * @property int            $user_type
 * @property int            $group_id
 * @property string         $user_permissions
 * @property string         $user_ip
 * @property \Carbon\Carbon $user_regdate
 * @property string         $username
 * @property \Carbon\Carbon $user_passchg
 * @property string         $user_email
 * @property string         $user_birthday
 * @property string         $user_password
 * @property \Carbon\Carbon $user_lastvisit
 * @property \Carbon\Carbon $user_lastpost_time
 * @property int            $user_warnings
 * @property int            $user_posts
 * @property float          $user_timezone
 * @property bool           $user_dst
 * @property string         $user_dateformat
 * @property int            $user_style
 * @property int            $user_rank
 * @property string         $user_occ
 * @property string         $user_colour
 * @property bool           $user_notify
 * @property bool           $user_notify_pm
 * @property int            $user_notify_type
 * @property bool           $user_allow_pm
 * @property bool           $user_allow_viewonline
 * @property bool           $user_allow_viewemail
 * @property bool           $user_allow_massemail
 * @property int            $user_inactive_reason
 *
 * @property string         $user_avatar
 * @property int            $user_avatar_type
 * @property string         $user_sig
 * @property string         $user_website
 *
 * @property string         $username_clean
 */
class User extends Model
{
    protected $primaryKey = 'user_id';

    protected $casts = [
        'user_regdate'          => 'datetime',
        'user_passchg'          => 'datetime',
        'user_lastvisit'        => 'datetime',
        'user_lastpost_time'    => 'datetime',
        'user_dst'              => 'boolean',
        'user_notify'           => 'boolean',
        'user_notify_pm'        => 'boolean',
        'user_allow_pm'         => 'boolean',
        'user_allow_viewonline' => 'boolean',
        'user_allow_viewemail'  => 'boolean',
        'user_allow_massemail'  => 'boolean',
    ];

    public function import(): ?AbstractModel
    {
        /** @var Flarum $user */
        $user = Flarum::with('groups')->findOrNew($this->getKey());

        $user->id = $this->getKey();
// @todo this doesn't work with a vanilla Flarum install, it needs a column..
//        $user->display_name = $this->username;

        if (! $user->exists) {
            /** @var UserValidator $validate */
            $validate = app(UserValidator::class);
            $validate->setUser($user);

            $username = $this->username_clean;

            $user->username = preg_replace(['/\s/', '/\W/'], '_', trim($username, ' '));

            if (empty($user->username)) {
                return null;
            }

            if ($user->newQuery()
                ->where('username', $user->username)
                ->where('id', '!=', $user->id)
                ->exists()) {
                $user->username .= $user->id;
            }

            try {
                $validate->assertValid($user->only('username'));
            } catch (ValidationException $e) {
                throw new InvalidArgumentException(
                    "Username validation failed: {$user->username}",
                    $e->getCode(),
                    $e
                );
            }
        }

        $user->joined_at          = $this->user_regdate;
        $user->email              = $this->getEmail();
        $user->password           = $this->user_password;
        $user->comment_count      = $this->user_posts;
        $user->avatar_url         = $this->user_avatar;
        $user->setPreference('disclose_online', $this->user_allow_viewonline);

        // local uploads
        if ($this->user_avatar_type === 1 && !Str::startsWith($this->user_avatar, 'https://')) {
            $user->avatar_url = "/avatars/" . $this->user_avatar;
        }

        $user->last_seen_at       = $this->user_lastvisit;
        $user->is_email_confirmed = true;
        // User bio offers not nearly the features we need, it's also removed from core.
        $user->bio = $this->user_occ;

        $user->save();

        if ($this->user_type === 3 && !$user->groups()->find(Group::ADMINISTRATOR_ID)) {
            $user->groups()->attach(Group::ADMINISTRATOR_ID);
        } elseif ($this->user_type === 1) {
            switch ($this->user_inactive_reason) {
                // not confirmed
                case 1:
                    $user->is_email_confirmed = false;
                    break;
                // manually set inactive
                case 3:
                case 0:
                    $user->suspended_until = (new Carbon())->addCentury();
                    break;
            }
        }

        $user->save();


        return $user;
    }

    public static function get(Flarum $user = null): ?User
    {
        return $user ? User::find($user->id) : null;
    }

    protected function getEmail()
    {
        $exists = Flarum::query()->where('email', $this->user_email)->where('id', '!=', $this->user_id)->exists();

        if ($exists) {
            list($prefix, $domain) = explode('@', $this->user_email);
            list($prefix, $subaddr) = explode('+', "$prefix+");

            return "$prefix+$subaddr{$this->user_id}@$domain";
        }

        return $this->user_email;
    }
}
