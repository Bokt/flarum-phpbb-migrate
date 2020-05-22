<?php

namespace Bokt\Phpbb\Models;

use Flarum\Database\AbstractModel;

/**
 * @property int $msg_id
 * @property int $root_level
 * @property int $author_id
 * @property string $author_ip
 * @property int $message_time
 * @property string $message_subject
 * @property string $message_text
 * @property int $message_edit_time
 * @property string $to_address
 * @property string $bcc_address
 */
class PrivateMessage extends Model
{
    public function import(): ?AbstractModel
    {
        // TODO: Implement import() method.
    }
}
