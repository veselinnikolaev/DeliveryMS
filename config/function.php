<?php

class Utility {

    static $order_status = [
        'pending' => 'Pending',
        'shipped' => 'Shipped',
        'out_for_delivery' => 'Out for Delivery',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        'returned' => 'Returned'
    ];
    static $currencies = [
        '$' => 'USD',
        '€' => 'EUR',
        '£' => 'GBP',
        'лв' => 'BGN',
        'C$' => 'CAD',
        'A$' => 'AUD',
        '¥' => 'JPY',
        '元' => 'CNY',
        '₣' => 'CHF'
    ];

    static function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }
}
