<?php

namespace App\Models;
use Core\Model;

class OrderDetail extends Model {

    var $primaryKey = 'id';
    var $table = 'order_details';

    var $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'order_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'product_id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'quantity', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'price', 'type' => 'decimal', 'default' => ':NULL')
    );
}
?>
