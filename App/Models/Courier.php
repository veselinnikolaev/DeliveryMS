<?php

namespace App\Models;
use Core\Model;

class Courier extends Model {

    var $primaryKey = 'id';
    var $table = 'couriers';

    var $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'name', 'type' => 'varchar', 'default' => ''),
        array('name' => 'phone_number', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'email', 'type' => 'varchar', 'default' => ':NULL')
    );
}
?>


