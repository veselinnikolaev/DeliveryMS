<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Notification extends Model
{
    public ?string $primaryKey = 'id';
    public ?string $table = 'notifications';

    public array $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ''),
        array('name' => 'user_id', 'type' => 'int', 'default' => ''),
        array('name' => 'message', 'type' => 'text', 'default' => ''),
        array('name' => 'link', 'type' => 'varchar', 'default' => 1),
        array('name' => 'is_seen', 'type' => 'int', 'default' => 0),
        array('name' => 'created_at', 'type' => 'varchar', 'default' => '')
    );
}
