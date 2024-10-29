<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Associator_Widget extends WP_Widget
{
    const SOURCE_BASKET = 'basket';
    const SOURCE_PRODUCT = 'product';
    const SOURCE_BASKET_OR_PRODUCT = 'basket-product';

    function __construct()
    {
        parent::__construct(
            'woocommerce_associator',
            __( 'Associator', 'associator' ),
            array('description' => __( 'Boost your sales with perfect product recommendations.', 'associator' ))
        );
    }

    public function widget($args, $instance)
    {
        $beforeWidget = isset($args['before_widget']) ? $args['before_widget'] : '';
        $afterWidget = isset($args['after_widget']) ? $args['after_widget'] : '';
        $max = isset($instance['max']) ? $instance['max'] : Associator::DEFAULT_MAX_RECOMMENDATIONS;
        $title = isset($instance['title']) ? $instance['title'] : '';
        $shortcode = sprintf('[associator max="%d"]%s[/associator]', $max, $title);

        echo $beforeWidget;
        echo do_shortcode(apply_filters( 'widget_text', $shortcode));
        echo $afterWidget;
    }

    public function form($instance)
    {
        $title = (isset($instance['title'])) ? $instance['title'] : __('Recommendations', 'associator');
        $max = $instance['max'] ? $instance['max'] : Associator::DEFAULT_MAX_RECOMMENDATIONS;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'associator'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"  name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('max'); ?>"><?php _e('The maximum number of recommendations:', 'associator'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('max'); ?>"  name="<?php echo $this->get_field_name( 'max' ); ?>" type="number" step="1" min="1" max="100" size="3" value="<?php echo esc_attr( $max ); ?>" />
        </p>
        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['max'] = ( ! empty( $new_instance['max'] ) ) ? strip_tags( $new_instance['max'] ) : '';

        return $instance;
    }
}

function associator_register_widgets()
{
    register_widget('Associator_Widget');
}

add_action('widgets_init', 'associator_register_widgets');
