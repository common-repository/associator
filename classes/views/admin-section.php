<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<style>
    #associator_button_export {
        display: none;
        margin-left: 1em;
    }
    .associator-loader {
        display: none;
    }
    .spinner {
	    float: none;
	    position: relative;
        top: -4px;
    }
    .associator-loader-content {
        margin: 5px 0;
    }
    .associator-info {
        display: none;
        margin-left: 1em;
    }
    .associator-info-icon {
	    position: relative;
        top: 5px;
    }
    .associator-info-content {
        margin: 5px 0;
    }
</style>

<span class="associator-button-container">

    <button id="associator_button_export" class="button"><?php _e( 'Export historic orders', 'associator' ) ?></button>

	<span class="associator-loader">
	    <span class="spinner"></span>
	    <span class="associator-loader-content"></span>
	</span>

	<span class="associator-info">
	    <span class="associator-info-icon"><span class="dashicons dashicons-admin-comments"></span></span>
	    <span class="associator-info-content"></span>
	</span>
</span>

<script>

    var variables = {
        translations: {
            exporting_transaction: "<?php _e( 'Exporting... Please don\'t leave this page until export is finished.', 'associator' ) ?>",
            something_went_wrong: "<?php _e( 'Oops, something went wrong. Please try again.', 'associator' ) ?>",
            export_finish_successfully: "<?php _e( 'Historic orders have been exported successfully! Recommendations will be displayed on your website after the data has been processed in our system (this may take several hours).', 'associator' ) ?>",
            application_active: "<?php _e( 'API key is active. Associator is working.', 'associator' ) ?>",
            application_invalid_api_key: "<?php _e( 'API key is invalid. Please enter a valid API key.', 'associator' ) ?>",
            application_inactive: "<?php _e( 'API key is inactive. This means your subscription has expired, has been cancelled or refunded.', 'associator' ) ?>",
        }
    };

    (function ($) {

		$(document).ready(function(){
			$('.associator-button-container').appendTo('p.submit');
		});

        $(document).ready(function(){
            if ($('#associator_settings\\[associator_api_key\\]').val() !== '') {

                // Load information about Associator status
                $.ajax({
                    url: 'admin-ajax.php?action=associator_application_status',
                    success: function (response) {
                        if (response.status === 'Error' && response.message === 'Application is not active') {
                            $('.associator-info-content').text(variables.translations.application_inactive);
                            $('.associator-info').show();
                        } else if (response.status === 'Error') {
                            $('.associator-info-content').text(variables.translations.application_invalid_api_key);
                            $('.associator-info').show();
                        } else {
                            $('.associator-info-content').text(variables.translations.application_active);
                            $('.associator-info').show()
                        }
                    }
                });

                // Show export button if there are more than 1000 unsynchronized transactions
                $.ajax({
                    url: 'admin-ajax.php?action=associator_is_synchronization_needed',
                    success: function (response) {
                        if (response.is_synchronization_needed === true) $('#associator_button_export').show();
                    }
                });
            }
        });

        $(document).on('click', '#associator_button_export', function (e) {
            e.preventDefault();

            $.ajax({
                type: 'post',
                url: 'admin-ajax.php',
                data: {action: 'associator_import_transactions'},
                timeout: 900000,
                beforeSend: function () {
                    $('#associator_button_export').hide();
                    $('.associator-loader-content').text(variables.translations.exporting_transaction);
                    $('.associator-loader').show();
                    $('.spinner').css('visibility', 'visible');
                },
                success: function (response) {
                    if (response.status === 'Success') {
                        $('.associator-loader').hide();
                        $('.spinner').css('visibility', 'hidden');
                        $('.associator-info-content').text(variables.translations.export_finish_successfully);
                        $('.associator-info').show();
                    } else {
                        $('.associator-loader').hide();
                        $('.spinner').css('visibility', 'hidden');
                        $('.associator-info-content').text(variables.translations.something_went_wrong);
                        $('.associator-info').show();
                    }
                },
                error: function() {
                    $('.associator-loader').hide();
                    $('.spinner').css('visibility', 'hidden');
                    $('.associator-info-content').text(variables.translations.something_went_wrong);
                    $('.associator-info').show();
                }
            });
        });

    })(jQuery);
</script>
