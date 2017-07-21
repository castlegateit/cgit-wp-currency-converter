<?php

namespace Cgit\CurrencyConverter;

class RateUpdater
{
    /**
     * Known currency codes
     *
     * @var array
     */
    public static $codes = [
        'AUD', 'BGN', 'BRL', 'CAD', 'CHF', 'CNY', 'CZK', 'DKK',
        'EUR', 'GBP', 'HKD', 'HRK', 'HUF', 'IDR', 'ILS', 'INR',
        'JPY', 'KRW', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN',
        'RON', 'RUB', 'SEK', 'SGD', 'THB', 'TRY', 'USD', 'ZAR',
    ];

    /**
     * Update exchange rates
     *
     * @return void
     */
    public function update()
    {
        global $wpdb;

        $base = 'http://api.fixer.io/latest?base=';

        foreach (self::$codes as $code) {
            $data = json_decode(file_get_contents($base . $code));

            // Bad data? Skip it.
            if (!$data) {
                continue;
            }

            foreach ($data->rates as $key => $rate) {
                $updated = $wpdb->update(Plugin::$table, [
                    'rate' => $rate,
                    'updated' => date('Y-m-d H:i:s'),
                ], [
                    'currency_from' => $code,
                    'currency_to' => $key,
                ]);

                // If a row has been updated, carry on to the next exchange
                // rate. Otherwise, insert a new row for this exchange rate.
                if ($updated) {
                    continue;
                }

                $wpdb->insert(Plugin::$table, [
                    'currency_from' => $code,
                    'currency_to' => $key,
                    'rate' => $rate,
                    'updated' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
