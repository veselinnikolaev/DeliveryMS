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

    static function generateRandomString($length = 10) {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    static function getDisplayableAmount($amount) {
        $settingModel = new Setting();
        $formattedAmount = number_format($amount, 2);

        $currency = $settingModel->getFirstBy(['key' => 'currency_code'])['value'];
        // Currencies that go before the amount
        $prefixCurrencies = ['$', '£', '¥', '₣'];

        return in_array($currency, $prefixCurrencies) ? "{$currency}{$formattedAmount}" : "{$formattedAmount} {$currency}";
    }
}
