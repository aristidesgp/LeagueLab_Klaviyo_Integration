<?php

/*
*
* @package aristidesgp
*
*/

namespace LLKI\Inc\Base;
use LLKI\Inc\Base\Logs;
class Enqueue
{

    public function register()
    {

        add_action('admin_enqueue_scripts',  array($this, 'LLKI_enqueue_frontend'));
        //add_action('wp_enqueue_scripts',  array($this, 'LLKI_enqueue_frontend'));
        
    }

    /**
     * Enqueueing the main scripts with all the javascript logic that this plugin offer
     */
    function LLKI_enqueue_frontend()
    {        
        wp_enqueue_style('main-css', LLKI_PLUGIN_URL . 'assets/css/main.css');
        wp_enqueue_script('main-js', LLKI_PLUGIN_URL  . 'assets/js/main.js', array('jquery'), '1.0', true);


        wp_localize_script('main-js', 'parameters', ['ajax_url' => admin_url('admin-ajax.php'), 'plugin_url' => LLKI_PLUGIN_URL]);
        wp_enqueue_script('checkout-js', LLKI_PLUGIN_URL  . 'assets/js/checkout.js', array('jquery', 'main-js'), '1.0', true);
    }
}
