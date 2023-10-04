<?php

/*
*
* @package aristidesgp
*
*/

namespace LLKI\Inc\Base;

class Schedule
{

    public function register(){        

        register_deactivation_hook(LLKI_PLUGIN_PATH, array($this, 'llki_deactivate_cron'));
		
        add_filter('cron_schedules', array($this, 'llki_add_custom_intervals'));

        add_action('init', array($this, 'llki_activate_cron'));
        
    }
    

    function llki_add_custom_intervals($schedules) {
        $schedules['llki_ten_minutes'] = array(
            'interval' => 600,
            'display' => esc_html__('Every 10 Minutes', LLKI_PLUGIN_PATH)
        );

        return $schedules;
    }
    
    public function llki_activate_cron() {    

        if (!wp_next_scheduled('llki_daily_sync_event')) {                     
            wp_schedule_event(strtotime('00:00:00'), 'daily', 'llki_daily_sync_event');
        }

        /* if ( ! wp_next_scheduled( 'llki_ten_minutes_event' ) ) {
            wp_schedule_event( time(), 'llki_ten_minutes', 'llki_ten_minutes_event' );
        }*/

        if ( ! wp_next_scheduled( 'llki_one_hour_event' ) ) {
            wp_schedule_event( time(), 'hourly', 'llki_one_hour_event' );
        }
    }

    function llki_deactivate_cron() {
        wp_clear_scheduled_hook('llki_daily_sync_event');
    }

    function llki_check_cron_registration() {

        $cron_schedule = wp_get_schedule('llki_daily_sync_event');
        if ($cron_schedule === false) {
            // The cron is not registered
            echo "The cron is not registered.";
            } else {
                // The cron is registered
                echo "The cron is registered. Schedule: " . $cron_schedule;
            }
        }
    
}
