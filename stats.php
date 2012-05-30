<?php if (!current_user_can('manage_options')) wp_die( __('You do not have sufficient permissions to access this page.') ); ?>
<div class="wrap">
<div class="icon32" style="background: url('<?php echo plugins_url('images/mandrill-head-icon.png',__FILE__); ?>');"><br /></div>
<h2><?php _e('Mandrill Service Report', wpMandrill::WPDOMAIN); ?></h2><?php

$stats = self::getCurrentStats();
if ( empty($stats) ) {
    echo '<p>' . __('There was a problem retrieving statistics.', self::WPDOMAIN) . '</p>';
    echo '</div>';
    return;
}

$delivered  = $stats['general']['stats']['sent'] -
                $stats['general']['stats']['hard_bounces'] - 
                $stats['general']['stats']['soft_bounces'] -
                $stats['general']['stats']['rejects'];

$lit = array();

$lit['hourly']['title']   = __('Hourly Sending Volume and Open/Click Rate',self::WPDOMAIN);
$lit['hourly']['Xtitle']  = __('Hours',self::WPDOMAIN);
$lit['hourly']['tooltip'] = __('Hour',self::WPDOMAIN);

$lit['daily']['title']    = __('Daily Sending Volume and Open/Click Rate',self::WPDOMAIN);
$lit['daily']['Xtitle']   = __('Days',self::WPDOMAIN);
$lit['daily']['tooltip']  = __('Day',self::WPDOMAIN);

$lit['subtitle']    = __('in the last 30 days',self::WPDOMAIN);
$lit['Ytitle']      = __('Open & Click Rate',self::WPDOMAIN);
$lit['SerieName']   = __('Volume',self::WPDOMAIN);
$lit['emails']      = __('emails',self::WPDOMAIN);
$lit['openrate']    = __('Open Rate',self::WPDOMAIN);
$lit['clickrate']   = __('Click Rate',self::WPDOMAIN);

?>
<div id="alltime_report">
    <h3><?php echo sprintf(__('All-time statistics since %s: ', wpMandrill::WPDOMAIN),date('m/d/Y',strtotime($stats['general']['created_at']))); ?></h3>
    
    <div id="alltime_report_canvas">
        <div class="stat_box"><?php _e('Reputation:', wpMandrill::WPDOMAIN); ?><br/><span><?=$stats['general']['reputation']?>%</span></div>
        <div class="stat_box"><?php _e('Quota:', wpMandrill::WPDOMAIN); ?><br/><span><?=$stats['general']['hourly_quota']?> <?php _e('sends/hour', wpMandrill::WPDOMAIN); ?></span></div>
        <div class="stat_box"><?php _e('Emails sent:', wpMandrill::WPDOMAIN); ?><br/><span><?=$stats['general']['stats']['sent']?></span></div>
        <div class="stat_box"><?php _e('Emails delivered:', wpMandrill::WPDOMAIN); ?><br/><span><?=$delivered?> (<?=number_format(  $delivered*100 / ( ($stats['general']['stats']['sent'])?$stats['general']['stats']['sent']:1 ) ,2); ?>%)</span></div>
        <div class="stat_box"><?php _e('Tracked opens:', wpMandrill::WPDOMAIN); ?><br/><span><?=$stats['general']['stats']['opens']?></span></div>
        <div class="stat_box"><?php _e('Tracked clicks:', wpMandrill::WPDOMAIN); ?><br/><span><?=$stats['general']['stats']['clicks']?></span></div>
        <?php
            if ( $stats['general']['stats']['rejects'] ) echo '<div class="stat_box warning">'.__('Rejects:', wpMandrill::WPDOMAIN).'<br/><span>'.$stats['general']['stats']['rejects'].'</span></div>';
            if ( $stats['general']['stats']['complaints'] ) echo '<div class="stat_box warning">'.__('Complaints:', wpMandrill::WPDOMAIN).'<br/><span>'.$stats['general']['stats']['complaints'].'</span></div>';
            if ( $stats['general']['backlog'] ) echo '<div class="stat_box warning">'.__('Current backlog:', wpMandrill::WPDOMAIN).'<br/><span>'.$stats['general']['backlog'].' emails</span></div>';
        ?>
    </div>
</div>

<div style="clear: both;"></div>
<div id="filtered_reports">
    <h3><?php _e('Filtered statistics:', wpMandrill::WPDOMAIN); ?></h3>
    <label for="filter"><?php _e('Filter by:', wpMandrill::WPDOMAIN); ?> </label>
    <select id="filter" name="filter">
        <option value="none" selected="selected" ><?php _e('No filter', wpMandrill::WPDOMAIN); ?></option>
        <optgroup label="<?php _e('Sender:', wpMandrill::WPDOMAIN); ?>">
            <?php 
                foreach ( array_keys($stats['stats']['hourly']['senders']) as $sender) {
                    echo '<option value="s:'.$sender.'">'.$sender.'</option>';
                }
            ?>            
        </optgroup>
        <optgroup label="<?php _e('Tag:', wpMandrill::WPDOMAIN); ?>">
            <?php 
                if ( isset($stats['stats']['hourly']['tags']['detailed_stats']) 
                     && is_array($stats['stats']['hourly']['tags']['detailed_stats']) ) {
                     
                    foreach ( array_keys($stats['stats']['hourly']['tags']['detailed_stats']) as $tag) {
                        echo '<option value="'.$tag.'">'.$tag.'</option>';
                    }
                    
                }
            ?>            
        </optgroup>        
    </select>
    <label for="display"><?php _e('Display:', wpMandrill::WPDOMAIN); ?> </label>
    <select id="display" name="display">
        <option value="volume"><?php _e('Total Volume per Period', wpMandrill::WPDOMAIN); ?></option>
        <option value="average"><?php _e('Average Volume per Period', wpMandrill::WPDOMAIN); ?></option>
    </select><div id="ajax-icon-container"><span id="loading_data" class="hidden"></span></div>
    <div id="filtered_reports_canvas">
        <div id="filtered_recent" style="width: 50%;float: left;"></div>
        <div id="filtered_oldest" style="width: 50%;float: left;"></div>
    </div>
    <div style="clear: both;"></div>
</div>
<br/><br/>
<div id="hourly_report">
<script type="text/javascript">
jQuery(function () {
    var chart;
    jQuery(document).ready(function() {
        chart = new Highcharts.Chart({
            chart: {
                renderTo: 'hourly_report_canvas',
                zoomType: 'xy',
                spacingBottom: 30
            },
            exporting: {
                enabled: true,
            },
            title: {
                text: '<?php echo $lit['hourly']['title']; ?>'
            },
            subtitle: {
                text: '<?php echo $lit['subtitle']; ?>'
            },
            xAxis: [{
                categories: [<?=implode(',',array_keys($stats['graph']['hourly']['delivered']));?>],
                title: {
                    enabled: true,
                    text: '<?php echo $lit['hourly']['Xtitle']; ?>',
                    style: {
                        fontWeight: 'normal'
                    }
            }
            }],
            yAxis: [{
                min: 0,
                max: 100,
                labels: {
                    formatter: function() {
                        return this.value +'%';
                    },
                    style: {
                        color: '#666666'
                    }
                },
                title: {
                    text: '<?php echo $lit['Ytitle']; ?>',
                    style: {
                        color: '#666666'
                    }
                }
            }, {
                title: {
                    text: '<?php echo $lit['SerieName']; ?>',
                    style: {
                        color: '#4572A7'
                    }
                },
                labels: {
                    formatter: function() {
                        return this.value +' <?php echo $lit['emails']; ?>';
                    },
                    style: {
                        color: '#4572A7'
                    }
                },
                opposite: true
            }],
            tooltip: {
                formatter: function() {
                    return '<?php echo $lit['hourly']['tooltip']; ?> '+
                        this.x +': '+ this.y +
                        (this.series.name == '<?php echo $lit['SerieName']; ?>' ? ' <?php echo $lit['emails']; ?>' : '%');
                }
            },
            plotOptions: {
			    column: {
				    fillOpacity: 0.5
			    }
		    },
		    credits: {
			    enabled: false,
			    href: "http://www.mandrillapp.com/",
			    text: "MandrillApp.com"
		    },
            legend: {
                align: 'right',
			    x: -100,
			    verticalAlign: 'top',
			    y: 20,
			    floating: true,
			    backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
			    borderColor: '#CCC',
			    borderWidth: 1,
			    shadow: false
            },
            series: [{
                name: 'Volume',
                color: '#4572A7',
                type: 'column',
                yAxis: 1,
                data: [<?=implode(',',$stats['graph']['hourly']['delivered']);?>],
                dataLabels: {
				    enabled: true,
				    rotation: -90,
				    color: '#FFFFFF',
				    align: 'right',
				    x: -3,
				    y: 10,
				    formatter: function() {
					    return this.y;
				    },
				    style: {
					    font: 'normal 13px Verdana, sans-serif'
				    }
			    },
            }, {
                name: '<?php echo $lit['openrate']; ?>',
                color: '#89A54E',
                type: 'spline',
                data: [<?=implode(',',$stats['graph']['hourly']['open_rate']);?>]
            }, {
                name: '<?php echo $lit['clickrate']; ?>',
                color: '#deA54E',
                type: 'spline',
                data: [<?=implode(',',$stats['graph']['hourly']['click_rate']);?>]
            }]
        });
    });
    chart = new Highcharts.Chart({
        chart: {
            renderTo: 'daily_report_canvas',
            zoomType: 'xy',
            spacingBottom: 30
        },
        title: {
            text: '<?php echo $lit['daily']['title']; ?>'
        },
        subtitle: {
            text: '<?php echo $lit['subtitle']; ?>'
        },
        xAxis: [{
            categories: [<?=implode(',',array_keys($stats['graph']['daily']['delivered']));?>],
            title: {
                enabled: true,
                text: '<?php echo $lit['daily']['Xtitle']; ?>',
                style: {
                    fontWeight: 'normal'
                }
            },
            labels: {
				rotation: -45,
				align: 'right',
				style: {
					font: 'normal 13px Verdana, sans-serif'
				}
			}
        }],
        yAxis: [{
            min: 0,
            max: 100,
            labels: {
                formatter: function() {
                    return this.value +'%';
                },
                style: {
                    color: '#666666'
                }
            },
            title: {
                text: '<?php echo $lit['Ytitle']; ?>',
                style: {
                    color: '#666666'
                }
            }
        }, {
            title: {
                text: '<?php echo $lit['SerieName']; ?>',
                style: {
                    color: '#4572A7'
                }
            },
            dataLabels: {
				enabled: true,
				rotation: -90,
				color: '#FFFFFF',
				align: 'right',
				x: -3,
				y: 10,
				formatter: function() {
					return this.y;
				},
				style: {
					font: 'normal 13px Verdana, sans-serif'
				}
			},
            labels: {
                formatter: function() {
                    return this.value +' <?php echo $lit['emails']; ?>';
                },
                style: {
                    color: '#4572A7'
                }
            },
            opposite: true
        }],
        tooltip: {
            formatter: function() {
                return '<?php echo $lit['daily']['tooltip']; ?> '+
                    this.x +': '+ this.y +
                    (this.series.name == '<?php echo $lit['SerieName']; ?>' ? ' <?php echo $lit['emails']; ?>' : '%');
            }
        },
        plotOptions: {
		    column: {
			    fillOpacity: 0.5
		    }
	    },
	    credits: {
		    enabled: false,
		    href: "http://www.mandrillapp.com/",
		    text: "MandrillApp.com"
	    },
        legend: {
            align: 'right',
		    x: -100,
		    verticalAlign: 'top',
		    y: 20,
		    floating: true,
		    backgroundColor: (Highcharts.theme && Highcharts.theme.legendBackgroundColorSolid) || 'white',
		    borderColor: '#CCC',
		    borderWidth: 1,
		    shadow: false
        },
        series: [{
            name: '<?php echo $lit['SerieName']; ?>',
            color: '#4572A7',
            type: 'column',
            yAxis: 1,
            data: [<?=implode(',',$stats['graph']['daily']['delivered']);?>],
            dataLabels: {
			    enabled: true,
			    rotation: -90,
			    color: '#FFFFFF',
			    align: 'right',
			    x: -3,
			    y: 10,
			    formatter: function() {
				    return this.y;
			    },
			    style: {
				    font: 'normal 13px Verdana, sans-serif'
			    }
		    },
        }, {
            name: '<?php echo $lit['openrate']; ?>',
            color: '#89A54E',
            type: 'spline',
            data: [<?=implode(',',$stats['graph']['daily']['open_rate']);?>]
        }, {
            name: '<?php echo $lit['clickrate']; ?>',
            color: '#deA54E',
            type: 'spline',
            data: [<?=implode(',',$stats['graph']['daily']['click_rate']);?>]
        }]
    });
});
</script>
    <div id="hourly_report_canvas"></div><br/><br/>
    <div id="daily_report_canvas"></div>
    <h3><a href="http://mandrillapp.com/" target="_target"><?php _e('For more detailed statistics, please visit your Mandrill Dashboard',self::WPDOMAIN); ?></a>.</h3>
</div>
		<?php
		wpMandrill::$stats = $stats;

?>
