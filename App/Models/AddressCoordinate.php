<?php

namespace App\Models;

use Core\Model;

class AddressCoordinate extends Model {

    var $primaryKey = 'id';
    var $table = 'courier_locations';
    var $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ''),
        array('name' => 'address_hash', 'type' => 'verchar', 'default' => ''),
        array('name' => 'address', 'type' => 'verchar', 'default' => ''),
        array('name' => 'latitude', 'type' => 'decimal', 'default' => ''),
        array('name' => 'longitude', 'type' => 'decimal', 'default' => ''),
        array('name' => 'timestamp', 'type' => 'bigint', 'default' => '')
    );
}

?>