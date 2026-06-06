<?php

declare(strict_types=1);

namespace App\Models;

use Core\Model;

class CourierLocation extends Model
{
    public ?string $primaryKey = 'id';
    public ?string $table = 'courier_locations';
    public array $schema = array(
        array('name' => 'id', 'type' => 'int', 'default' => ''),
        array('name' => 'user_id', 'type' => 'int', 'default' => ''),
        array('name' => 'latitude', 'type' => 'decimal', 'default' => ''),
        array('name' => 'longitude', 'type' => 'decimal', 'default' => ''),
        array('name' => 'timestamp', 'type' => 'bigint', 'default' => '')
    );

    public function getLatestLocation($courierId): array
    {
        return $this->getAll(['user_id' => $courierId], 'timestamp DESC')[0];
    }
}
