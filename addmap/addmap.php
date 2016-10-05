<?php
/*
Plugin Name: addmap
Description: Whereabouts (google map API): Addmap
Version: 0.0.8
Author: HalcheSM
Author URI: http://

	Copyright 2016  HalcheSM  (email: testapiw@gmail.com)
*/


define( 'ADDMAP_DIR', plugin_dir_path(__FILE__));
define( 'ADDMAP_URL', plugin_dir_url(__FILE__));
define( 'ADDMAP_ASSETS', plugin_dir_url(__FILE__) . '/assets/');  


 if ( ! function_exists( 'whereabouts_addmap_setup' ) ) {

	function whereabouts_addmap_setup() {

		// Define include path for this plugin
		define( 'ADDMAP_DIR', plugin_dir_path( __FILE__ ) );

		// Define url for this plugin
		define( 'ADDMAP_URL', plugin_dir_url( __FILE__ ) );

                define( 'ADDMAP_ASSETS', plugin_dir_url(__FILE__) . '/assets/'); 
                
		// Get location
		require ADDMAP_DIR . '/includes/get-location.php';
                
                if (is_admin()) {
                
                    require_once(ADDMAP_DIR.'/includes/class.addmap-admin.php');
                    
                    add_action( 'init', array( 'Addmap_Admin', 'init' ) );  
                }
	
                require_once(ADDMAP_DIR . '/includes/class.addmap.php' );

                //add_action( 'init', array( 'Addmap', 'init' ) );
                
                Addmap::init();
                
                require ADDMAP_DIR . '/includes/class.addmap-widget.php';
                
                
	}

}

add_action( 'after_setup_theme', 'whereabouts_addmap_setup' );
 

//load_plugin_textdomain( 'whereabouts-addmap', false, basename( dirname( __FILE__ ) ) . '/languages' );


