# Castlegate IT WP Currency Converter

Provides currency conversion using the [Fixer.io](http://fixer.io/) API. The plugin updates the exchange rates stored in the database daily using WP cron. Conversions are performed by instances of the `Cgit\CurrencyConverter\Converter` class:

~~~ php
use Cgit\CurrencyConverter\Converter;
$cc = new Converter(25, 'GBP'); // set the initial value to £25
~~~

You can change the base currency, the initial value, or both at the same time:

~~~ php
$cc->setCurrency('EUR');
$cc->setValue(50);
$cc->set(50, 'EUR');
~~~

You can get the value in another currency by specifying its three-letter currency code and an optional number of decimal places:

~~~ php
echo $cc->get('USD'); // floating point decimal
echo $cc->get('USD', 2); // formatted with two decimal places
~~~

You can also return the original value and currency code:

~~~ php
echo $cc->getCurrency();
echo $cc->getValue();
~~~

The object can also return a complete set of exchange rates for the base currency:

~~~ php
$rates = $cc->getRates();
~~~

See [Fixer.io](http://fixer.io/) for a complete list of valid currency codes.

## License

Copyright (c) 2019 Castlegate IT. All rights reserved.

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License along with this program. If not, see <https://www.gnu.org/licenses/>.
