<?php

namespace Cgit\CurrencyConverter;

class Converter
{
    /**
     * Base currency
     *
     * @var string
     */
    private $currency;

    /**
     * Value in base currency
     *
     * @var float
     */
    private $value;

    /**
     * Constructor
     *
     * Assign numerical value and currency code to properties.
     *
     * @param integer|float $value
     * @param string $currency
     * @return void
     */
    public function __construct($value = 1, $currency = 'EUR')
    {
        $this->set($value, $currency);
    }

    /**
     * Set the value and currency
     *
     * @param integer|float $value
     * @param string $currency
     * @return void
     */
    public function set($value, $currency = 'EUR')
    {
        $this->setValue($value);
        $this->setCurrency($currency);
    }

    /**
     * Set value
     *
     * @param integer|float $value
     * @return void
     */
    public function setValue($value)
    {
        $this->value = self::sanitizeValue($value);
    }

    /**
     * Set currency
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = self::sanitizeCurrency($currency);
    }

    /**
     * Get value in base currency
     *
     * @return integer|float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Get value in another currency
     *
     * Return the value in another currency based on the exchange rates stored
     * in the database. The second parameter formats the output to a particular
     * number of decimal places.
     *
     * @param string $currency
     * @param int $format
     * @return mixed
     */
    public function get($currency, $format = null)
    {
        global $wpdb;

        $table = Plugin::$table;
        $from = $this->currency;
        $to = self::sanitizeCurrency($currency);
        $converted = 0;

        // If the currency code cannot be sanitized, there is nothing more we
        // can do here.
        if (!$to) {
            return false;
        }

        // If the requested currency is the same as the original currency, we
        // can avoid a lot of hard work.
        if ($from == $to) {
            $converted = $this->value;
        } else {
            // Get the exchange rate from the database and, if it exists, use it
            // to calculate the value in the new currency.
            $rate = $wpdb->get_var("
                SELECT rate FROM $table
                WHERE currency_from = '$from'
                AND currency_to = '$to'
            ");

            if (!$rate) {
                return false;
            }

            $converted = $this->value * $rate;
        }

        // If no format is specified, return the converted value as a floating
        // point decimal.
        if (is_null($format)) {
            return $converted;
        }

        // Return the format with a fixed number of decimal places.
        return number_format($converted, $format);
    }

    /**
     * Get exchange rates
     *
     * Returns an associative array of exchange rates for the current currency
     * based on the data available in the database.
     *
     * @return array
     */
    public function getRates()
    {
        global $wpdb;

        $table = Plugin::$table;
        $currency = $this->currency;
        $rates = [];

        // Extract the relevant rows from the database
        $results = $wpdb->get_results("
            SELECT * FROM $table
            WHERE currency_from = '$currency'
        ");

        // And extract the relevant information from the rows
        foreach ($results as $result) {
            $rates[$result->currency_to] = $result->rate;
        }

        return $rates;
    }

    /**
     * Sanitize value
     *
     * Make sure the value is a number, not a string. If the value is an integer
     * or a float, return in unmodified. If it is a string, strip any
     * non-numerical characters and convert it to a float.
     *
     * @param mixed $value
     * @return string
     */
    private static function sanitizeValue($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value)) {
            return floatval(preg_replace('/[^0-9\.]/', '', $value));
        }

        trigger_error('Value must be a number');
        return false;
    }

    /**
     * Sanitize currency code
     *
     * A currency code can only be one of the three-letter, uppercase currency
     * codes in the list of valid codes. Lowercase values will be converted to
     * uppercase. If the code is not recognized, an error is triggered and the
     * method returns false.
     *
     * @param string $input
     * @return string
     */
    private static function sanitizeCurrency($input)
    {
        $code = strtoupper($input);

        if (!in_array($code, RateUpdater::$codes)) {
            trigger_error('Unknown currency: ' . $code);
            return false;
        }

        return $code;
    }
}
