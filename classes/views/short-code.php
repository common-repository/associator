<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="associator-container related products">
    <h3><?php echo $content; ?></h3>
    <?php if (isset($attributes['columns'])): ?>
        <?php echo do_shortcode(apply_filters( 'widget_text', sprintf('[products ids="%s" limit="%d" columns="%d"]', implode(',', $associations), $attributes['max'], $attributes['columns']))); ?>
    <? else: ?>
        <?php echo do_shortcode(apply_filters( 'widget_text', sprintf('[products ids="%s" limit="%d"]', implode(',', $associations), $attributes['max']))); ?>
    <? endif; ?>
</div>

<script>
    (function ($) {

        // Action when user click to recommendation
        $('.woocommerce-loop-product__link').click(function(event) {

            if (!$(event.target).parents('.associator-container').length) {
                return;
            }

            if (!$(this).siblings("[data-product_id]").length) {
                return;
            }

            $.ajax({
                type: 'post',
                url: '/wp-admin/admin-ajax.php',
                async: false,
                data: {
                    action: 'associator_ajax_event',
                    event: 'click',
                    products: [$(this).siblings("[data-product_id]").data('product_id')]
                }
            });
        });

        // Action when user add product to cart from recommendation
        $('.add_to_cart_button').click(function(event) {

            if (!$(event.target).parents('.associator-container').length) {
                return;
            }

            $.ajax({
                type: 'post',
                url: '/wp-admin/admin-ajax.php',
                async: false,
                data: {
                    action: 'associator_ajax_event',
                    event: 'add',
                    products: [$(event.target).data('product_id')]
                }
            });
        });

    })(jQuery);
</script>