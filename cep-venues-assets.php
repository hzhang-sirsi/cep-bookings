<?php
/*
Plugin Name: CEP Venues and Assets
Description: Provides tracking for room availability and asset tracking
Version: 0.0.1
Author: SirsiDynix
Author URI: http://sirsidynix.com
Text Domain: cep-venues-assets
License: Proprietary
*/

namespace SirsiDynix\CEPVenuesAssets;

require __DIR__ . '/bootstrap.php';

Plugin::initialize(__DIR__ . '/cep-venues-assets.php');
