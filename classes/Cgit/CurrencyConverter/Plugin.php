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
     * Exchange rates database table name
     *
     * @var string
     */
    public static $table;

    /**
     * Exchange rate updater class instance
     *
     * @var RateUpdater
     */
    private $updater;

    /**
     * Constructor
     *
     * Registers the activation and deactivation methods and creates an action
     * that will be used to update the exchange rates stored in the database.
     *
     * @return void
     */
    public function __construct()
    {
        global $wpdb;

        $plugin = CGIT_CURRENCY_CONVERTER_PLUGIN;
        self::$table = $wpdb->base_prefix . 'cgit_currency_codes';
        $this->updater = new RateUpdater();

        // Register activation and deactivation methods to create the required
        // database tables and add or remove the scheduled tasks.
        register_activation_hook($plugin, [$this, 'activate']);
        register_deactivation_hook($plugin, [$this, 'deactivate']);

        // Register an action to update the exchange rates in the database.
        add_action($this->action, [$this->updater, 'update']);
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
}
