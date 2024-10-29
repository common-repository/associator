<?php
/** @var WPDesk_Plugin_Info $plugin_info */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPDesk_Loader_Manager_Factory' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/wpdesk/wp-autoloader/src/Loader/Loader_Manager_Factory.php';
}

if ( ! class_exists( 'WPDesk_Composer_Loader' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/wpdesk/wp-autoloader/src/Loader/Composer/Composer_Loader.php';
}

if ( ! class_exists( 'WPDesk_Composer_Loader_Info' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/wpdesk/wp-autoloader/src/Loader/Composer/Composer_Loader_Info.php';
}

$loader_info = new WPDesk_Composer_Loader_Info();
$loader_info->set_autoload_file( new \SplFileInfo( realpath( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) );
$loader_info->set_load_priority( $plugin_info->get_release_date()->getTimestamp() );
$loader_info->set_creation_file( new \SplFileInfo( realpath( dirname( __FILE__ ) . '/plugin-create.php' ) ) );
$loader_info->set_plugin_info($plugin_info);

$composer_loader = new WPDesk_Composer_Loader($loader_info);

$loader_manager = WPDesk_Loader_Manager_Factory::get_manager_instance();
$loader_manager->attach_loader($composer_loader);