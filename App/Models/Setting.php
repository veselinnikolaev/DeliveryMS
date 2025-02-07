<?php

namespace App\Models;

use Core\Model;

class Setting extends Model {

    var $primaryKey = 'id';
    var $table = 'settings';
    
    var $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'tax_rate', 'type' => 'decimal(10,2)', 'default' => ':NULL'),
        array('name' => 'shipping_rate', 'type' => 'decimal(10,2)', 'default' => ':NULL'),
        array('name' => 'currency_code', 'type' => 'varchar(3)', 'default' => ''),
        array('name' => 'email_sending', 'type' => 'tinyint(1)', 'default' => ''),
        array('name' => 'delivery_time_days', 'type' => 'int', 'default' => '')
    );
}
?>

