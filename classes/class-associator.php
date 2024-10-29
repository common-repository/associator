<?php

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Associator
 */
class Associator
{
    const EXPORT_FIELD_NAME = 'associator_export';
    const REPORT_FIELD_NAME = 'associator_report';
    const DEFAULT_MAX_RECOMMENDATIONS = 3;
    const TRANSACTIONS_BATCH_SIZE = 1000;
    const CACHE_NAME_PATTERN = 'associator_%s_%s_%s';
    const CACHE_EXPIRATION = 900;

    /** @var \Associator\Associator */
    private $associator;

    /** @var Associator_Event_Repository */
    private $eventRepository;

    /**
     * Associator constructor.
     * @param $associator
     * @param $eventRepository
     */
    public function __construct($associator, $eventRepository)
    {
        $this->associator = $associator;
        $this->eventRepository = $eventRepository;
    }

    /**
	 * Initialize
	 */
	public function init()
    {
        $this->register_action();
        $this->register_cron();
        $this->register_shortcode();
        $this->display_options();
	}

    /**
     * Register actions
     */
	public function register_action()
    {
        add_action('init',[$this, 'register_session']);
        add_action('wp_cron_associator_synchronize_transaction', [$this, 'synchronize_transaction']);
        add_action('wp_ajax_associator_is_synchronization_needed', [$this, 'api_is_synchronization_needed']);
        add_action('wp_ajax_associator_import_transactions', [$this, 'api_synchronize_transaction']);
        add_action('wp_ajax_associator_application_status', [$this, 'api_application_status']);
        add_action('wp_ajax_associator_report', [$this, 'api_statistic_data']);
        add_action('wp_ajax_associator_ajax_event', [$this, 'api_save_event']);
        add_action('wp_ajax_nopriv_associator_ajax_event', [$this, 'api_save_event']);
        add_action('woocommerce_thankyou', [$this, 'order_status_completed']);
    }

    /**
     * Register cron job
     */
    public function register_cron()
    {
        if (! wp_next_scheduled ( 'wp_cron_associator_synchronize_transaction' )) {
            wp_schedule_event(time(), 'hourly', 'wp_cron_associator_synchronize_transaction');
        }
    }

    /**
     * @return string|null
     */
    private function get_associator_key()
    {
        $settings = get_option('associator_settings', []);

        return isset($settings['associator_api_key']) ? $settings['associator_api_key'] : null;
    }

    /**
     * Register shortcode
     */
    public function register_shortcode()
    {
        add_shortcode('associator', [$this, 'associator_shortcode']);
    }

    /**
     * Handle display options action
     */
    public function display_options()
    {
        $settings = get_option('associator_settings', []);

        if (isset($settings['associator_single_product_recommendations']) && boolval($settings['associator_single_product_recommendations'])) {
            add_action( 'woocommerce_after_single_product_summary', function () {
                $title = __( 'Customers Who Bought This Item Also Bought', 'associator' );
                $shortcode = sprintf('[associator max="%d" source="%s"]%s[/associator]', self::DEFAULT_MAX_RECOMMENDATIONS, Associator_Widget::SOURCE_PRODUCT, $title);
                echo do_shortcode(apply_filters( 'widget_text', $shortcode));
            }, 20 );
        }

        if (isset($settings['associator_hide_related_products']) && boolval($settings['associator_hide_related_products'])) {
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
        }

        if (isset($settings['associator_cart_recommendations']) && boolval($settings['associator_cart_recommendations'])) {
            add_action( 'woocommerce_cart_collaterals', function () {
                $title = __( 'You may be interested in&hellip;', 'associator' );
                $shortcode = sprintf('[associator max="%d"]%s[/associator]', self::DEFAULT_MAX_RECOMMENDATIONS, $title);
                echo do_shortcode(apply_filters( 'widget_text', $shortcode));
            }, 20 );
        }

        if (isset($settings['associator_hide_cross_sells']) && boolval($settings['associator_hide_cross_sells'])) {
            remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
        }
    }

    /**
     * Register session if not start already
     */
    public function register_session()
    {
        if(!session_id()) {
            session_start();
        }
    }

    /**
     * Get products form basket
     * @return array
     */
    public function get_products_from_cart()
    {
        $products = [];

        if (null === WC()->cart) {
            return $products;
        }

        foreach (WC()->cart->get_cart() as $cartItem => $values) {
            if ($values['quantity'] > 0) {
                $products[] = $values['product_id'];
            }
        }

        return $products;
    }

    /**
     * Render recommendations using short code
     * @param $attributes
     * @param string $content
     * @return void
     */
    function associator_shortcode($attributes, $content = '')
    {
        $attributes = shortcode_atts([
            'columns' => null,
            'max' => self::DEFAULT_MAX_RECOMMENDATIONS,
            'source' => Associator_Widget::SOURCE_BASKET_OR_PRODUCT
        ], $attributes);

        $settings = get_option('associator_settings', []);

        if (!isset($settings['associator_api_key'])) {
            return null;
        }

        $samples = [];

        if ($attributes['source'] === Associator_Widget::SOURCE_BASKET_OR_PRODUCT) {
            $samples = $this->get_products_from_cart();

            if (empty($samples) && is_product()) {
                $samples = [wc_get_product()->get_id()];
            }
        }

        if ($attributes['source'] === Associator_Widget::SOURCE_BASKET) {
            $samples = $this->get_products_from_cart();
        }

        if ($attributes['source'] === Associator_Widget::SOURCE_PRODUCT) {
            if (is_product()) {
                $samples = [wc_get_product()->get_id()];
            }
        }

        // Return if basket is empty
        if (empty($samples)) {
            return null;
        }

        $this->associator->setApiKey($settings['associator_api_key']);
        //Get support and confidence and normalize
        $support = isset($settings['associator_support']) ? floatval($settings['associator_support']) / 100 : null;
        $confidence = isset($settings['associator_confidence']) ? floatval($settings['associator_confidence']) / 100 : null;

        $cacheKey = sprintf(self::CACHE_NAME_PATTERN, json_encode($samples), $support, $confidence);

        if (false === ($associations = get_transient($cacheKey))) {
            try {
                $response = $this->associator->getAssociations($samples, $support, $confidence);
            } catch (\Associator\Exception\AssociatorException $exception) {
                return null;
            }
            if (isset($response['status']) && $response['status'] === 'Error') {
                return null;
            }

            $associations = isset($response['associations']) ? $response['associations'] : [];
            $associations = array_reduce($associations, 'array_merge', array());

            set_transient($cacheKey, $associations, self::CACHE_EXPIRATION);
        }

        // Return if no associations found
        if (empty($associations)) {
            return null;
        }

        $this->eventRepository->persist(Associator_Event_Repository::EVENT_TYPE_SHOW, $associations);

        include('views/short-code.php');
    }

    /**
     * Order status completed action
     * @param integer $order_id
     * @return void
     */
    function order_status_completed($order_id)
    {
        $settings = get_option('associator_settings', []);

        if (!isset($settings['associator_api_key'])) {
            return;
        }

        $this->associator->setApiKey($settings['associator_api_key']);
        $order = new WC_Order($order_id);

        // Prepare array with product ids from order
        $items = array_map(function ($item) { return $item->get_product_id(); }, $order->get_items());
        try {
            $response = $this->associator->saveTransaction(array_values($items));
        } catch (\Associator\Exception\AssociatorException $exception) {
            return;
        }

        // Add information about persist in Associator
        if (isset($response['status']) && $response['status'] === 'Success') {
            add_post_meta($order->get_id(), self::EXPORT_FIELD_NAME, $settings['associator_api_key']);
        }

        if (!isset($_SESSION['associator'])) {
            return;
        }

        foreach ($_SESSION['associator'] as $product) {
            if (in_array($product, $items)) {
                $report[] = intval($product);
            }
        }

        $this->eventRepository->persist(
            Associator_Event_Repository::EVENT_TYPE_BUY,
            array_unique($report)
        );

        unset($_SESSION['associator']);
    }

    /**
     * Synchronize transaction with Associator system
     * @return boolean
     */
    public function synchronize_transaction()
    {
        $orders = [];
        $transactions = [];
        $associatorKey = $this->get_associator_key();

        if (is_null($associatorKey)) {
            return false;
        }

        $this->associator->setApiKey($associatorKey);

        for ($i = 0; count($orders) < self::TRANSACTIONS_BATCH_SIZE; $i+=250) {

            $results = wc_get_orders(['limit' => 250, 'offset' => $i]);

            if (empty($results)) {
                break;
            }

            $orders = array_merge($orders, array_filter($results, function ($order) use ($associatorKey) {
                return !in_array($associatorKey, get_post_meta($order->get_id(), self::EXPORT_FIELD_NAME));
            }));
        }

        if (empty($orders)) {
            return true;
        }

        // Prepare batch of orders for export
        $orders = array_slice($orders, 0, self::TRANSACTIONS_BATCH_SIZE);

        // Extract order items
        foreach ($orders as $order) {
            $transactions[] = array_map(function ($item) { return $item->get_product_id(); }, $order->get_items());
        }

        // Send transactions to Associator API
        try {
            $response = $this->associator->importTransactions($transactions);
        } catch (\Associator\Exception\AssociatorException $exception) {
            return false;
        }

        if (!isset($response['status']) || (isset($response['status']) && $response['status'] === 'Error')) {
            return false;
        }

        // Mark orders as synchronized
        foreach ($orders as $order) {
            add_post_meta($order->get_id(), self::EXPORT_FIELD_NAME, $associatorKey);
        }

        $this->eventRepository->persist(Associator_Event_Repository::EVENT_TYPE_SYNCHRONIZED, [
            'count' => count($orders)
        ]);

        return true;
    }

    //======================================================================
    // PLUGIN API
    //======================================================================

    /**
     * API endpoints returns application status
     */
    public function api_application_status()
    {
        $settings = get_option('associator_settings', []);

        if (!isset($settings['associator_api_key'])) {
            wp_send_json(['status' => 'Error']);
        }

        $this->associator->setApiKey($settings['associator_api_key']);
        try {
            $response = $this->associator->getAssociations([]);
        } catch (\Associator\Exception\AssociatorException $exception) {
            return null;
        }

        if (isset($response['status']) && $response['status'] === 'Error' && isset($response['message'])) {
            wp_send_json(['status' => 'Error', 'message' => $response['message']]);
        }

        if (isset($response['status']) && $response['status'] === 'Error') {
            wp_send_json(['status' => 'Error']);
        }

        wp_send_json(['status' => 'Success']);
    }

    /**
     * API endpoint return information about synchronization
     */
    public function api_is_synchronization_needed()
    {
        $orders = [];
        $associatorKey = $this->get_associator_key();

        for ($i = 0; count($orders) < self::TRANSACTIONS_BATCH_SIZE; $i+=250) {

            $results = wc_get_orders(['limit' => 250, 'offset' => $i]);

            if (empty($results)) {
                wp_send_json(['status' => 'Success', 'is_synchronization_needed' => false]);
                exit;
            }

            $orders = array_merge($orders, array_filter($results, function ($order) use ($associatorKey) {
                return !in_array($associatorKey, get_post_meta($order->get_id(), self::EXPORT_FIELD_NAME));
            }));
        }

        wp_send_json(['status' => 'Success', 'is_synchronization_needed' => true]);
    }

    /**
     * API endpoint to manual synchronize transactions
     */
    public function api_synchronize_transaction()
    {
        $this->synchronize_transaction() ? wp_send_json(['status' => 'Success']) : wp_send_json(['status' => 'Error']);
    }

    /**
     * API endpoint persist information about event
     */
    function api_save_event()
    {
        $event = isset($_POST['event']) ? $_POST['event'] : null;
        $products = isset($_POST['products']) && is_array($_POST['products']) ? $_POST['products'] : [];

        if (!in_array($event, $this->eventRepository->getAllowedEvents())) {
            return;
        }

        if ($event === Associator_Event_Repository::EVENT_TYPE_ADD) {
            $this->register_session();
            $_SESSION['associator'] = isset($_SESSION['associator']) ? array_merge($_SESSION['associator'], $products) : $products;
        }

        $this->eventRepository->persist($event, array_map('intval', $products));
    }

    /**
     * API endpoint returns statistic data
     */
    public function api_statistic_data()
    {
        $from = new DateTime($_GET['from']);
        $to = new DateTime($_GET['to']);
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($from, $interval, $to);

        foreach ($period as $key => $value) {

            // DATE LABELS
            $label[] = $value->format('d-m');

            // SHOW SERIES
            $events = $this->eventRepository->getByEventAndDate(
                Associator_Event_Repository::EVENT_TYPE_SHOW,
                $value->format('Y-m-d')
            );
            $items = array_map(function ($event) { return count(json_decode($event['value']));}, $events);
            $showSeries[] = array_sum($items);

            // ADD SERIES
            $events = $this->eventRepository->getByEventAndDate(
                Associator_Event_Repository::EVENT_TYPE_ADD,
                $value->format('Y-m-d')
            );
            $items = array_map(function ($event) { return count(json_decode($event['value']));}, $events);
            $addSeries[] = array_sum($items);

            // BUY SERIES
            $events = $this->eventRepository->getByEventAndDate(
                Associator_Event_Repository::EVENT_TYPE_BUY,
                $value->format('Y-m-d')
            );
            $items = array_map(function ($event) { return count(json_decode($event['value']));}, $events);
            $buySeries[] = array_sum($items);

            // CLICK SERIES
            $events = $this->eventRepository->getByEventAndDate(
                Associator_Event_Repository::EVENT_TYPE_CLICK,
                $value->format('Y-m-d')
            );
            $items = array_map(function ($event) { return count(json_decode($event['value']));}, $events);
            $clickSeries[] = array_sum($items);

            // ORDER SERIES
            $orders = wc_get_orders(['date_created' => $value->format('Y-m-d'), 'limit' => -1]);
            $count = array_map(function ($order) { return count($order->get_items()); }, $orders);
            $orderSeries[] = array_sum($count);
        }

        wp_send_json([
            'status' => 'Success',
            'charts' => [
                'orders' => [
                    'labels' => $label,
                    'series' => [$orderSeries],
                ],
                'views' => [
                    'labels' => $label,
                    'series' => [$showSeries],
                ],
                'add' => [
                    'labels' => $label,
                    'series' => [$addSeries],
                ],
                'click' => [
                    'labels' => $label,
                    'series' => [$clickSeries],
                ],
                'buy' => [
                    'labels' => $label,
                    'series' => [$buySeries],
                ]
            ]
        ]);
    }
}
