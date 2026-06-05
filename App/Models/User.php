<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class User extends Model {

    public ?string $primaryKey = 'id';
    public ?string $table = 'users';

    public array $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'name', 'type' => 'varchar', 'default' => ''),
        array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'phone_number', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'password_hash', 'type' => 'varchar', 'default' => ''),
        array('name' => 'created_at', 'type' => 'timestamp', 'default' => 'CURRENT_TIMESTAMP'),
        array('name' => 'role', 'type' => 'varchar', 'default' => ''),
        array('name' => 'address', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'country', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'region', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'photo_path', 'type' => 'varchar', 'default' => ':NULL')
    );
}