<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Product extends Model
{
    public ?string $primaryKey = 'id';
    public ?string $table = 'products';

    public array $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'name', 'type' => 'varchar', 'default' => ''),
        array('name' => 'description', 'type' => 'text', 'default' => ':NULL'),
        array('name' => 'price', 'type' => 'decimal', 'default' => ':NULL'),
        array('name' => 'stock', 'type' => 'int', 'default' => 0),
        array('name' => 'created_at', 'type' => 'timestamp', 'default' => 'CURRENT_TIMESTAMP')
    );
}
