<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Associator_Event_Repository
{
    const EVENT_TYPE_SHOW = 'show';
    const EVENT_TYPE_CLICK = 'click';
    const EVENT_TYPE_ADD = 'add';
    const EVENT_TYPE_BUY = 'buy';
    const EVENT_TYPE_SYNCHRONIZED = 'synchronized';

    /**
     * Return allowed type of events
     * @return array
     */
    public function getAllowedEvents()
    {
        return [
            self::EVENT_TYPE_SHOW,
            self::EVENT_TYPE_CLICK,
            self::EVENT_TYPE_ADD,
            self::EVENT_TYPE_BUY,
            self::EVENT_TYPE_SYNCHRONIZED
        ];
    }

    /**
     * Save event in database
     * @param string $event
     * @param array $items
     * @return bool
     */
   public function persist($event, array $items)
   {
       global $wpdb;

       if (!in_array($event, $this->getAllowedEvents())) {
           return false;
       }

       $wpdb->insert($wpdb->prefix.'associator_event', [
           'type' => $event,
           'value' => json_encode($items),
           'created_at' => current_time('mysql'),
       ]);

       return true;
   }

    /**
     * Get events by type and date
     * @param $event
     * @param $date
     * @return mixed
     */
    public function getByEventAndDate($event, $date)
    {
        global $wpdb;

        $table = sprintf('%sassociator_event', $wpdb->prefix);
        $query = "SELECT * FROM {$table} WHERE type = %s AND DATE(created_at) = %s;";
        $results = $wpdb->get_results($wpdb->prepare($query, $event, $date), ARRAY_A);

        return $results;
    }
}

