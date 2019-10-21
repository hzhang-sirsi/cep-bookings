<?php
/*
Plugin Name: CEP Bookings
Description: Provides tracking for room availability and asset tracking
Version: 0.0.2
Author: SirsiDynix
Author URI: http://sirsidynix.com
Text Domain: cep-bookings
License: Proprietary
*/

namespace SirsiDynix\CEPBookings;

require __DIR__ . '/bootstrap.php';

Plugin::initialize(__DIR__ . '/cep-bookings.php');
