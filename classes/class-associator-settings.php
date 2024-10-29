<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Reports settings
 */
class Associator_Settings
{
	/**
	 * Initialize
	 */
	public function init()
    {
		add_filter( 'associator_menu', [ $this, 'settings_menu' ] );
        add_filter( 'associator_settings_tabs', [ $this, 'settings_tabs' ] );
        add_filter( 'associator_registered_settings_tabs', [ $this, 'registered_settings_tabs' ] );
		add_filter( 'associator_registered_settings_sections', [ $this, 'registered_settings_sections' ] );
		add_filter( 'associator_registered_settings', [ $this, 'registered_settings' ] );
		add_action( 'associator_settings_tab_bottom_settings_settings', [ $this, 'generate_admin_section' ] );
        add_action( 'associator_settings_tab_top_reports_reports', [ $this, 'generate_admin_report' ] );
	}

	/**
	 * Prepares menu item for settings
	 * @param array $menu
	 * @return array
	 */
	public function settings_menu(array $menu)
    {
		$menu['type']       = 'menu';
		$menu['page_title'] = __( 'Associator', 'associator' );
		$menu['show_title'] = true;
		$menu['menu_title'] = __( 'Associator', 'associator' );
		$menu['capability'] = 'manage_options';
		$menu['icon']       = 'dashicons-chart-line';
		$menu['position']   = null;

		return $menu;
	}

    /**
     * Prepares menu item for settings
     * @param array $menu
     * @return array
     */
    public function settings_tabs()
    {
        $tabs = [
            'settings' => __( 'Settings', 'associator' ),
            'options' => __( 'Display options', 'associator' ),
            'reports' => __( 'Reports', 'associator' ),

        ];

        return $tabs;
    }

    /**
     * Registers sesttings sections
     * @return array
     */
    public function registered_settings_sections()
    {
        $sections = [
            'settings' => [
                'settings' => __( 'Settings', 'associator' ),
            ],
            'options' => [
                'options' => __( 'Display options', 'associator' ),
            ],
            'reports' => [
                'reports' => __( 'Reports', 'associator' ),
            ]
        ];

        return $sections;
    }

	/**
	 * Register reports settings.
	 * @param array $settings
	 * @return array
	 */
	public function registered_settings($settings)
    {
	    $docs_link = 'https://www.wpdesk.net/docs/associator/';

	    if ( get_locale() === 'pl_PL' ) {
			$docs_link = 'https://www.wpdesk.pl/docs/associator/';
	    }

		$plugin_settings = [
			'settings' => [
				'settings' => [
					[
						'id' => 'associator_getting_started',
						'name' => __('Getting Started', 'associator'),
						'type' => 'descriptive_text',
                        'desc' => sprintf( __( 'Read the docs to get started with Associator. %sGo to docs &rarr;%s', 'associator' ), '<a href="' . $docs_link . '" target="_blank">', '</a>' )
					],
					[
						'id' => 'associator_api_key',
						'name' => __('API key', 'associator'),
						'type' => 'text',
                        'tooltip_title' => __('API key', 'associator'),
                        'tooltip_desc' => __('API key is required to use Associator platform. You won\'t see any recomendations until you register for a key.', 'associator'),
					],
                    [
                        'id' => 'associator_support',
                        'name' => __('Support', 'associator'),
                        'type' => 'number',
                        'min' => 1,
                        'max' => 100,
                        'step' => 1,
                        'tooltip_title' => __('Support', 'associator'),
                        'tooltip_desc' => __('How popular a product set is. Measured by the percentage of orders in which a product set exists.', 'associator'),
                    ],
                    [
                        'id' => 'associator_confidence',
                        'name' => __('Confidence', 'associator'),
                        'type' => 'number',
                        'min' => 1,
                        'max' => 100,
                        'step' => 1,
                        'tooltip_title' => __('Confidence', 'associator'),
                        'tooltip_desc' => __('How likely a product is purchased when other products are purchased.', 'associator'),
                    ]
				]
			],
            'options' => [
                'options' => [
                    [
                        'id' => 'associator_single_product_recommendations',
                        'name' => __('Single product recommendations', 'associator'),
                        'type' => 'checkbox',
                        'tooltip_title' => __('Single product recommendations', 'associator'),
                        'tooltip_desc' => __('Display recommendations on single product pages.', 'associator'),
                    ],
                    [
                        'id' => 'associator_hide_related_products',
                        'name' => __('Hide related products', 'associator'),
                        'type' => 'checkbox',
                        'tooltip_title' => __('Hide related products', 'associator'),
                        'tooltip_desc' => __('Hide default WooCommerce related products on single product pages.', 'associator'),
                    ],
                    [
                        'id' => 'associator_cart_recommendations',
                        'name' => __('Cart recommendations', 'associator'),
                        'type' => 'checkbox',
                        'tooltip_title' => __('Cart recommendations', 'associator'),
                        'tooltip_desc' => __('Display recommendations on the cart page.', 'associator'),
                    ],
                    [
                        'id' => 'associator_hide_cross_sells',
                        'name' => __('Hide cross sells', 'associator'),
                        'type' => 'checkbox',
                        'tooltip_title' => __('Hide cross sells', 'associator'),
                        'tooltip_desc' => __('Hide default WooCommerce cross sells on the cart page.', 'associator'),
                    ],
                ]
            ],
            'reports' => [
                'reports' => []
            ]
		];

		return array_merge($settings, $plugin_settings);
	}

	public function generate_admin_section()
    {
	    $terms_link = 'https://www.wpdesk.net/associator/terms/';
	    $privacy_link = 'https://www.wpdesk.net/associator/data/';

	    if ( get_locale() === 'pl_PL' ) {
	    	$terms_link = 'https://www.wpdesk.pl/associator/regulamin/';
	    	$privacy_link = 'https://www.wpdesk.pl/associator/dane/';
	    }

	    $orders_exported = get_option( 'associator_orders_exported' );
		include( 'views/admin-section.php' );
	}

    public function generate_admin_report()
    {
        include( 'views/admin-report.php' );
    }
}
