<?php

/*

Plugin Name: Castlegate IT WP Currency Converter
Plugin URI: http://github.com/castlegateit/cgit-wp-currency-converter
Description: Currency conversion via Fixer.io
Version: 1.1
Author: Castlegate IT
Author URI: http://www.castlegateit.co.uk/
License: AGPL

*/

if (!defined('ABSPATH')) {
    wp_die('Access denied');
}

define('CGIT_CURRENCY_CONVERTER_PLUGIN', __FILE__);

require_once __DIR__ . '/classes/autoload.php';

$plugin = new \Cgit\CurrencyConverter\Plugin();

do_action('cgit_currency_converter_loaded');
