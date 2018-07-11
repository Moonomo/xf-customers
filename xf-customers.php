<?php
/**
 * Plugin Name: Customers Lead Gen by Omaar Osmaan
 * Plugin URI: https://xfrontend.com/wordpress-plugins/
 * Description: A Simple Lead Gen Form Plugin.
 * Author: xFrontend
 * Author URI: https://xfrontend.com/
 * License: GPL v2 or Later
 * License URI: http://www.gnu.org/licenses/gpl.html
 * Text Domain: xf-customers
 * Domain Path: /languages/
 * Version: 0.0.1
 */

/**
 * Copyright (C) 2018, Omaar Osmaan <https://moonomo.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace XFrontend\Customers;

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


/**
 * @param string $file_query_name
 */
function autoload( $file_query_name ) {

	if ( strpos( $file_query_name, __NAMESPACE__ ) !== 0 ) {
		return;
	}

	// Exclude base namespace length
	$file_query_name_parts = explode( '\\', substr( $file_query_name, 19 ) );
	$class                 = array_pop( $file_query_name_parts );
	$last_namespace        = $file_query_name_parts ? array_pop( $file_query_name_parts ) : '';
	$namespace             = $file_query_name_parts ? implode( DIRECTORY_SEPARATOR, $file_query_name_parts ) . DIRECTORY_SEPARATOR . $last_namespace : $last_namespace;
	$class_file            = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $last_namespace . $class ) );

	$folder = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
	if ( $namespace ) {
		$folder .= $namespace . DIRECTORY_SEPARATOR;
	}

	if ( ! is_readable( $folder . "class-{$class_file}.php" ) ) {
		return;
	}

	/** @noinspection PhpIncludeInspection */
	include_once $folder . "class-{$class_file}.php";
}

spl_autoload_register( __NAMESPACE__ . '\\autoload' );


/**
 * Load the plugin files.
 */
add_action( 'plugins_loaded', function () {

	$plugin = new Plugin( __FILE__ );
	$plugin->setup();

	$shortcodes = new Shortcodes( $plugin );
	$shortcodes->setup();

	$service = new Service( $plugin );
	$service->setup();
} );
