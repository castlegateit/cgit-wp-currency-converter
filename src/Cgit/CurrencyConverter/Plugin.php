<?php

namespace Cgit\CurrencyConverter;

class Plugin
{
    /**
     * Name for update action
     *
     * @var string
     */
    private $action = 'cgit_currency_converter_update';

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
     * Exchange rates table name
     *
     * @var string
     */
    public static $table;

    /**
     * Singleton class instance
     *
     * @var Plugin
     */
    private static $instance;

    /**
     * Private constructor
     *
     * Registers the activation and deactivation methods and creates an action
     * that will be used to update the exchange rates stored in the database.
     *
     * @return void
     */
    private function __construct()
    {
        global $wpdb;

        // Database table names
        self::$table = $wpdb->prefix . 'cgit_currency_codes';

        // Path to main plugin file
        $plugin = CGIT_CURRENCY_CONVERTER_FILE;

        // Register activation and deactivation methods to create the required
        // database tables and add or remove the scheduled tasks.
        register_activation_hook($plugin, [$this, 'activate']);
        register_deactivation_hook($plugin, [$this, 'deactivate']);

        // Register an action to update the exchange rates in the database.
        add_action($this->action, [$this, 'update']);
    }

    /**
     * Return the singleton class instance
     *
     * @return Plugin
     */
    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Activation
     *
     * Create the required database tables, update the list of currencies in the
     * database, and schedule a task to update the exchange rates.
     *
     * @return void
     */
    public function activate()
    {
        // Register the scheduled task
        wp_schedule_event(time(), 'daily', $this->action);

        // Create the required table(s)
        $this->createDatabaseTables();
    }

    /**
     * Deactivation
     *
     * Remove the scheduled task that updates the exchange rates in the
     * database.
     *
     * @return void
     */
    public function deactivate()
    {
        wp_clear_scheduled_hook($this->action);
    }

    /**
     * Create database tables
     *
     * @return void
     */
    public function createDatabaseTables()
    {
        global $wpdb;

        $wpdb->query('CREATE TABLE IF NOT EXISTS ' . self::$table . ' (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            currency_from CHAR(3),
            currency_to CHAR(3),
            rate FLOAT,
            updated DATETIME
        )');
    }

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
                $updated = $wpdb->update(self::$table, [
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

                $wpdb->insert(self::$table, [
                    'currency_from' => $code,
                    'currency_to' => $key,
                    'rate' => $rate,
                    'updated' => date('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
