<?php
/*
Plugin Name: On This Day (by Room 34)
Plugin URI: https://wordpress.org/plugins/room-34-presents-on-this-day/
Description: A very simple widget that displays a list of blog posts that were published on the same date in previous years. Title and "no posts" message are customizable.
Version: 3.4.0
Requires at least: 5.0
Requires PHP: 7.0
Author: Room 34 Creative Services, LLC
Author URI: http://room34.com
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: r34otd
Domain Path: /i18n/languages/
*/

/*
  Copyright 2024 Room 34 Creative Services, LLC (email: info@room34.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as 
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
*/


// Don't load directly
if (!defined('ABSPATH')) { exit; }


// Load required files
require_once(plugin_dir_path(__FILE__) . '/admin.php');
require_once(plugin_dir_path(__FILE__) . '/archive.php');
require_once(plugin_dir_path(__FILE__) . '/functions.php');
require_once(plugin_dir_path(__FILE__) . '/widget.php');


// Flush rewrite rules when plugin is activated
register_activation_hook(__FILE__, function() { flush_rewrite_rules(); });
