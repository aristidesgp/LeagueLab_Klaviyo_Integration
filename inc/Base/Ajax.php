<?php

/*
*
* @package aristidesgp
*
*/

namespace LLKI\Inc\Base;

use LLKI\Inc\Base\Sincro;
class Ajax
{

    public function register()
    {

        /**
         * Ajax actions
         */ 
        add_action('wp_ajax_manual_sync', array($this, 'manual_sync_handler'));       
    }

    public function manual_sync_handler() {
        $sync=new Sincro();
        $data=$sync->llki_run_daily_sync();
        echo json_encode(array('success' => true,'data' => $data));
        wp_die();
    }
    
}
