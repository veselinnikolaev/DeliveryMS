<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class OrderProducts extends Model {

    public ?string $primaryKey = 'id';
    public ?string $table = 'order_products';

    public array $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'order_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'quantity', 'type' => 'int', 'default' => 1),
        array('name' => 'price', 'type' => 'decimal', 'default' => ':NULL'),
        array('name' => 'subtotal', 'type' => 'decimal', 'default' => ':NULL')
    );

}
