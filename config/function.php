<?php

use App\Models\Setting;

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
        'lv' => 'BGN',
        'C$' => 'CAD',
        'A$' => 'AUD',
        '¥' => 'JPY',
        '元' => 'CNY',
        '₣' => 'CHF'
    ];
    static $dateFormats = [
        'm/d/Y' => '03/24/2025', // MM/DD/YYYY
        'd/m/Y' => '24/03/2025', // DD/MM/YYYY
        'Y-m-d' => '2025-03-24', // YYYY-MM-DD
        'd-m-Y' => '24-03-2025', // DD-MM-YYYY
        'm-d-Y' => '03-24-2025', // MM-DD-YYYY
        'l, F j, Y' => 'Monday, March 24, 2025', // Day, Month Date, Year
    ];

    static function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    static function getDisplayableAmount($amount) {
        $settingModel = new Setting();
        $currency = $settingModel->getFirstBy(['key' => 'currency_code'])['value'];

        // Currencies that go before the amount
        $prefixCurrencies = ['$', '£', '¥', '₣'];

        // Remove any commas and cast to float
        $amount = floatval(str_replace(',', '', $amount));

        $formattedAmount = number_format($amount, 2);

        return in_array($currency, $prefixCurrencies) ? "{$currency}{$formattedAmount}" : "{$formattedAmount} {$currency}";
    }
}
