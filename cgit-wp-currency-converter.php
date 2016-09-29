<?php

/*

Plugin Name: Castlegate IT WP Currency Converter
Plugin URI: http://github.com/castlegateit/cgit-wp-currency-converter
Description: Currency conversion via Fixer.io
Version: 1.0
Author: Castlegate IT
Author URI: http://www.castlegateit.co.uk/
License: MIT

*/

use Cgit\CurrencyConverter\Plugin;

// Constants
define('CGIT_CURRENCY_CONVERTER_FILE', __FILE__);

// Load plugin
require_once __DIR__ . '/src/autoload.php';

// Initialization
Plugin::getInstance();
