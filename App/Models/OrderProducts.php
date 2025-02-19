<?php

namespace App\Models;

use Core\Model;

class OrderProducts extends Model {

    var $primaryKey = 'id';
    var $table = 'order_products';
    
    var $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'order_id', 'type' => 'int', 'default' => ':NULL'), // Foreign key to orders table
        array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'), // Foreign key to products table
        array('name' => 'quantity', 'type' => 'int', 'default' => 1),
        array('name' => 'price', 'type' => 'decimal', 'default' => ':NULL'), // Store product price at the time of order
        array('name' => 'subtotal', 'type' => 'decimal', 'default' => ':NULL') // Calculated as quantity * price
    );

}
?>
