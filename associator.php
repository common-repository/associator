<?php
/*
	Plugin Name: Associator
	Plugin URI: https://www.wpdesk.net/products/associator/
	Description: Boost your sales with perfect product recommendations.
	Version: 1.0.7
	Author: WP Desk
	Author URI: https://www.wpdesk.net/
    Developer: Tomasz Tarnawski
    Developer URI: https://www.ttarnawski.usermd.net
	Text Domain: associator
	Domain Path: /lang/
	Requires at least: 4.5
    Tested up to: 4.9.8
    WC requires at least: 3.1.0
    WC tested up to: 3.6

	Copyright 2018 WP Desk Ltd.

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPDesk_Basic_Requirement_Checker' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/wpdesk/wp-basic-requirements/src/Basic_Requirement_Checker.php';
}

/* THESE TWO VARIABLES CAN BE CHANGED AUTOMATICALLY */
$plugin_version           = '1.0.7';
$plugin_release_timestamp = '2019-04-19';

$plugin_name        = 'Associator';
$plugin_class_name  = 'Associator_Plugin';
$plugin_text_domain = 'associator';

define( $plugin_class_name, $plugin_version );

$requirements_checker = new WPDesk_Basic_Requirement_Checker(
	__FILE__,
	$plugin_name,
	$plugin_text_domain,
	'5.6',
	'4.5'
);

if ( $requirements_checker->are_requirements_met() ) {

	if ( ! class_exists( 'WPDesk_Plugin_Info' ) ) {
		require_once dirname( __FILE__ ) . '/vendor/wpdesk/wp-basic-requirements/src/Plugin/Plugin_Info.php';
	}

	$plugin_info = new WPDesk_Plugin_Info();
	$plugin_info->set_plugin_file_name( plugin_basename( __FILE__ ) );
	$plugin_info->set_plugin_dir( dirname( __FILE__ ) );
	$plugin_info->set_class_name( $plugin_class_name );
	$plugin_info->set_version( $plugin_version );
	$plugin_info->set_product_id( $plugin_text_domain );
	$plugin_info->set_text_domain( $plugin_text_domain );
	$plugin_info->set_release_date( new DateTime( $plugin_release_timestamp ) );
	$plugin_info->set_plugin_url( plugins_url( dirname( plugin_basename( __FILE__ ) ) ) );

	require_once dirname( __FILE__ ) . '/plugin-load.php';
} else {
	$requirements_checker->disable_plugin_render_notice();
}
