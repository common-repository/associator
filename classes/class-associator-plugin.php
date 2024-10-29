<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Main class for WordPress plugin
 */
class Associator_Plugin extends \WPDesk\PluginBuilder\Plugin\AbstractPlugin
{
	/** @var Associator  */
	public $associator = null;

	/** @var Associator_Settings  */
	public $associator_settings = null;

	/** @var string */
	public $plugin_path;

    /** @var string */
	public $template_path;

    /** @var string */
	public $plugin_text_domain;

    /** @var string */
	public $default_settings_tab;

    /** @var array */
	public $default_view_args;

    /** @var string */
	public $scripts_version = '10';

	/** @var string */
	public $database_version = '1.0.1';

	/**
	 * Associator_Plugin constructor.
	 * @param WPDesk_Plugin_Info $plugin_info
	 */
	public function __construct(WPDesk_Plugin_Info $plugin_info)
    {
		$this->plugin_info = $plugin_info;
		parent::__construct($this->plugin_info);
	}

	/**
	 * Initialize base variables for plugin
	 */
	public function init_base_variables()
    {
		$this->plugin_url           = $this->plugin_info->get_plugin_url();
		$this->plugin_path          = $this->plugin_info->get_plugin_dir();
		$this->template_path        = $this->plugin_info->get_text_domain();
		$this->plugin_text_domain   = $this->plugin_info->get_text_domain();
		$this->plugin_namespace     = $this->plugin_info->get_text_domain();
		$this->template_path        = $this->plugin_info->get_text_domain();
		$this->default_settings_tab = 'settings';
		$this->settings_url         = admin_url('admin.php?page=associator-settings&tab=settings');
		$this->docs_url             = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/docs/associator' : 'https://www.wpdesk.net/docs/associator';
		$this->default_view_args    = ['plugin_url' => $this->get_plugin_url()];
	}

    public function hooks()
    {
        add_action( 'plugins_loaded', [$this, 'database_prepare'] );
        parent::hooks();
    }

    /**
     * Initialize plugin
     */
	public function init()
    {
		parent::init();

        new WPDesk_WP_Settings($this->get_plugin_url(), $this->get_namespace(), $this->default_settings_tab);

        $this->associator_settings = new Associator_Settings();
        $this->associator_settings->init();

        $client = new \Associator\Client();
        $associator = new \Associator\Associator($client);

        $eventRepository = new Associator_Event_Repository();
        $this->associator = new Associator($associator, $eventRepository);
        $this->associator->init();

        require_once('class-associator-widget.php' );

	}

	/**
	 * Initialize scripts
	 */
	public function admin_enqueue_scripts()
    {
        wp_register_script( 'associator-chartist', trailingslashit( $this->get_plugin_assets_url() ) . 'chartist-js/chartist.min.js', array(), $this->scripts_version);
        wp_register_style( 'associator-chartist', trailingslashit( $this->get_plugin_assets_url() ) . 'chartist-js/chartist.css', array(), $this->scripts_version);
        wp_enqueue_script( 'associator-chartist' );
        wp_enqueue_style( 'associator-chartist' );

        wp_register_script( 'associator-moment', trailingslashit( $this->get_plugin_assets_url() ) . 'moment/moment.min.js', array(), $this->scripts_version);
        wp_enqueue_script( 'associator-moment' );

        wp_register_script( 'associator-daterangepicker', trailingslashit( $this->get_plugin_assets_url() ) . 'daterangepicker/daterangepicker.js', array(), $this->scripts_version);
        wp_register_style( 'associator-daterangepicker', trailingslashit( $this->get_plugin_assets_url() ) . 'daterangepicker/daterangepicker.css', array(), $this->scripts_version);
        wp_enqueue_style( 'associator-daterangepicker' );
        wp_enqueue_script( 'associator-daterangepicker' );
	}

	public function database_prepare()
    {
        global $wpdb;

        if ($this->database_version === get_option('associator_database_version')) {
            return;
        }

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta("CREATE TABLE {$wpdb->prefix}associator_event (
            id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
            type VARCHAR(30) NOT NULL,
            value VARCHAR(255) NOT NULL,
            created_at TIMESTAMP
        )");

        update_option('associator_database_version' , $this->database_version);
    }
}
