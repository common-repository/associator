<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<style>
    #submit {
        display: none;
    }
    .associator-analytics-container {
        display: none;
    }
    .associator-analytics-container .associator-analytics-item {
        width: 100%;
        margin-bottom: 25px;
    }
    .associator-analytics-container .associator-analytics-item-half {
        width: 50%;
        margin-bottom: 25px;
        float: left;
    }
    .associator-analytics-container .associator-analytics-chart-title {
        color: #23282d;
        padding: 25px 25px;
        font-size: 23px;
        float: left;
    }
    .associator-analytics-container .associator-analytics-chart-menu{
        padding: 25px 25px;
        float: right;
    }
    .associator-analytics-container .associator-analytics-item-half .ct-series-a .ct-line {
        stroke: #058DC7;
    }
    .associator-analytics-container .associator-analytics-item .ct-series-a .ct-bar {
        stroke: #058DC7;
    }
    .associator-analytics-container .associator-analytics-item .ct-series-b .ct-bar {
        stroke: #50B432;
    }
    .associator-loader {
        margin-top: 50px;
        width: 100%;
        text-align: center;
    }
    .associator-loader-spinner {
        background: url('/wp-admin/images/wpspin_light-2x.gif') no-repeat;
        background-size: 25px 25px;
        visibility: visible;
        opacity: .7;
        filter: alpha(opacity=70);
        width: 25px;
        height: 25px;
        display: block;
        margin-left: auto;
        margin-right: auto;
    }
    .associator-loader-content {
        font-size: 16px;
        margin: 5px 0;
    }
</style>
<div class="associator-loader">
    <div class="associator-loader-spinner"></div>
    <div class="associator-loader-content"><?php _e( 'Generating reports...', 'associator' ) ?></div>
</div>
<div class="associator-analytics-container">
    <div class="associator-analytics-item">
        <div class="associator-analytics-chart-title"><?php _e( 'All orders', 'associator' ) ?></div>
        <div class="associator-analytics-chart-menu">
            <label for="associator-datarange"><?php _e( 'Date range:', 'associator' ) ?></label>
            <input id="associator-datarange" type="text" name="daterange" />
        </div>
        <div class="ct-chart-sales"></div>
    </div>
    <div class="associator-analytics-item-half">
        <div class="associator-analytics-chart-title"><?php _e( 'Views of recommendations', 'associator' ) ?></div>
        <div class="ct-chart-view"></div>
    </div>
    <div class="associator-analytics-item-half">
        <div class="associator-analytics-chart-title"><?php _e( 'Clicks on recommendations', 'associator' ) ?></div>
        <div class="ct-chart-click"></div>
    </div>
    <div class="associator-analytics-item-half">
        <div class="associator-analytics-chart-title"><?php _e( 'Added to cart from recommendations', 'associator' ) ?></div>
        <div class="ct-chart-cart"></div>
    </div>
    <div class="associator-analytics-item-half">
        <div class="associator-analytics-chart-title"><?php _e( 'Bought from recommendations', 'associator' ) ?></div>
        <div class="ct-chart-bought"></div>
    </div>
</div>

<script>

    (function ($) {

        fetchData(moment().subtract(13, 'days').format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));

        $('input[name="daterange"]').daterangepicker({
            opens: 'left',
            startDate: moment().subtract(13, 'days'),
            endDate: moment(),
            ranges: {
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 14 Days': [moment().subtract(13, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, function(start, end, label) {
            fetchData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        });

        function fetchData(from_date, to_date) {
            $.ajax({
                type: 'get',
                url: 'admin-ajax.php',
                data: {
                    action: 'associator_report',
                    from: from_date,
                    to: to_date
                },
                success: function (response) {
                    if (response.status === 'Success') {

                        $('.associator-loader').hide();
                        $('.associator-analytics-container').show();

                        new Chartist.Bar('.ct-chart-sales', {
                            labels: response.charts.orders.labels,
                            series: response.charts.orders.series
                        }, {
                            height: 500,
                            axisY: {
                                onlyInteger: true
                            }
                        });

                        new Chartist.Line('.ct-chart-view', {
                            labels: response.charts.views.labels,
                            series: response.charts.views.series
                        }, {
                            showPoint: false,
                            axisY: {
                                onlyInteger: true
                            }
                        });

                        new Chartist.Line('.ct-chart-click', {
                            labels: response.charts.click.labels,
                            series: response.charts.click.series
                        }, {
                            showPoint: false,
                            axisY: {
                                onlyInteger: true
                            }
                        });

                        new Chartist.Line('.ct-chart-cart', {
                            labels: response.charts.add.labels,
                            series: response.charts.add.series
                        }, {
                            showPoint: false,
                            axisY: {
                                onlyInteger: true
                            }
                        });

                        new Chartist.Line('.ct-chart-bought', {
                            labels: response.charts.buy.labels,
                            series: response.charts.buy.series
                        }, {
                            showPoint: false,
                            axisY: {
                                onlyInteger: true
                            }
                        });
                    }
                }
            });
        }

    })(jQuery);

</script>
