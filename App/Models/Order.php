<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class Order extends Model
{
    public ?string $primaryKey = 'id';
    public ?string $table = 'orders';

    public array $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'user_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'address', 'type' => 'varchar', 'default' => ''),
        array('name' => 'country', 'type' => 'varchar', 'default' => ''),
        array('name' => 'region', 'type' => 'varchar', 'default' => ''),
        array('name' => 'status', 'type' => 'varchar', 'default' => ''),
        array('name' => 'product_price', 'type' => 'decimal', 'default' => ':NULL'),
        array('name' => 'tax', 'type' => 'decimal', 'default' => ':NULL'),
        array('name' => 'shipping_price', 'type' => 'decimal', 'default' => ':NULL'),
        array('name' => 'total_amount', 'type' => 'decimal', 'default' => ':NULL'),
        array('name' => 'created_at', 'type' => 'varchar', 'default' => ''),
        array('name' => 'last_processed', 'type' => 'varchar', 'default' => ''),
        array('name' => 'courier_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'tracking_number', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'delivery_date', 'type' => 'varchar', 'default' => ':NULL')
    );
}
