<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Setting extends Model {

    public ?string $primaryKey = 'id';
    public ?string $table = 'settings';

    public array $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'key', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'value', 'type' => 'varchar', 'default' => ':NULL'),
    );
}

