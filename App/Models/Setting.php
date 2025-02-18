<?php

namespace App\Models;

use Core\Model;

class Setting extends Model {

    var $primaryKey = 'id';
    var $table = 'settings';
    
    var $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ':NULL'),
        array('name' => 'key', 'type' => 'varchar', 'default' => ':NULL'),
        array('name' => 'value', 'type' => 'varchar', 'default' => ':NULL'),
    );
}
?>

