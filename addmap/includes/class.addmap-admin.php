<?php

class Addmap_Admin {

    private static $initiated = false;

    public static function init() {

           if ( !self::$initiated ) {

            self::init_hooks();

           }

    }

    public static function init_hooks() {
            self::$initiated = true;

            add_action('admin_init', array( 'Addmap_Admin', 'addmap_settings_init' ));

            add_action('admin_menu', array( 'Addmap_Admin', 'addmap_admin_menu_setup' ));

            add_filter( 'plugin_action_links', array( 'Addmap_Admin','addmap_plugin_links'), 2, 2 );

            register_uninstall_hook( __FILE__, 'uninstall_addmap' );
    }
	

       
        
        
    /* register menu item */
    function addmap_admin_menu_setup() {
        add_submenu_page(
            'options-general.php',
            'Addmap Settings',
            'AddmapSet',
            'manage_options',
            'addmap',
            array( 'Addmap_Admin', 'addmap_admin_page_screen')
        );
    }




	
    /* display page content */
    function addmap_admin_page_screen() {
        global $submenu;

        // access page settings
        $page_data = array();

        foreach ($submenu['options-general.php'] as $i => $menu_item) {
            if ($submenu['options-general.php'][$i][2] == 'addmap') {
                $page_data = $submenu['options-general.php'][$i];
            }
        }
        // output
    ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2><?php echo $page_data[3]; ?></h2>
            <form id="addmap_options" action="options.php" method="post">
                <?php
                settings_fields('addmap_options');
                do_settings_sections('addmap');

                $options = get_option( 'addmap_options' );

                $show_venue   = ( !empty($options['show_venue']) ) ?$options['show_venue']: 'on';
                $show_city    = ( !empty($options['show_city'] )) ?$options['show_city']: 'on';
                $show_country = ( !empty($options['show_country'] )) ?$options['show_country']: 'on';
                $show_state   = ( !empty($options['show_state'] )) ?$options['show_state']: 'on';

                //$show_time_zone = ( !empty($options['show_time_zone'] )) ?$options['show_time_zone']: 'on';

                //$link_venue_website = ( !empty($options['link_venue_website'] )) ?$options['link_venue_website'] : 'off';

                $key_google = ( !empty($options['key_google'] )) ?$options['key_google'] : '';

                ?>
                <fieldset id="display_settings">
                        <legend>addmap</legend>
                        <h4>Settings Addmap</h4>
                        <p>
                                <span class="nowrap"><input type="checkbox" name="addmap_options[show_venue]" id="show_venue"<?php checked( 'on', $show_venue, true ); ?> /> <label class="after" for="show_venue">Venue</label></span>
                                <span class="nowrap"><input type="checkbox" name="addmap_options[show_city]" id="show_city"<?php checked( 'on', $show_city, true ); ?> /> <label class="after" for="show_city">City</label></span>
                                <span class="nowrap"><input type="checkbox" name="addmap_options[show_country]" id="show_country"<?php checked( 'on', $show_country, true ); ?> /> <label class="after" for="show_country">Country</label></span>
                                <span class="nowrap"><input type="checkbox" name="addmap_options[show_state]" id="show_state"<?php checked( 'on', $show_state, true ); ?> /> <label class="after" for="show_state">State</label></span>
                                
                                <!--span class="nowrap"><input type="checkbox" name="addmap_options[show_time_zone]" id="show_time_zone"<?php checked( 'on', $show_time_zone, true ); ?> /> <label class="after" for="show_time_zone">Time Zone</label></span//-->
                        </p>
                        <!--p><input type="checkbox" name="addmap_options[link_venue_website]" id="link_venue_website"<?php checked( 'on', $link_venue_website, true ); ?><?php if ( $show_venue == 'off' ) { echo ' disabled="disabled"'; } ?> /> <label class="after" for="link_venue_website">Link venue to its own website<br /><span class="description">This will link your last check-in to the venue\'s website, if a URL is listed on foursquare.</span></label></p//-->

                        <p><label class="after" for="key_google">Google API key</label>
                           <input type="text" name="addmap_options[key_google]" id="key_google" value="<?php echo $key_google; ?>"></p>
                        <?php submit_button('Save options', 'primary', 'addmap_options_submit') ?>
                </fieldset>            


            </form>
        </div>
        <?php
    }	
	



    /* регистрация настроек в системе */
    function addmap_settings_init() {
        register_setting('addmap_options', 'addmap_options',
            array('Addmap_Admin', 'addmap_options_validate')
        );
    }



    /* обработка ввода */
    function addmap_options_validate($args) {
        global $allowedposttags, $allowedrichhtml;
 
	// Sanitize show_venue
	if ( ! isset( $args['show_venue'] ) OR 'on' != $args['show_venue'] ) {
		$args['show_venue'] = 'off';
	}

	// Sanitize show_city
	if ( ! isset( $args['show_city'] ) OR 'on' != $args['show_city'] ) {
		$args['show_city'] = 'off';
	}

	// Sanitize show_country
	if ( ! isset( $args['show_country'] ) OR 'on' != $args['show_country'] ) {
		$args['show_country'] = 'off';
	}

	// Sanitize show_time_zone
	if ( ! isset( $args['show_time_zone'] ) OR 'on' != $args['show_time_zone'] ) {
		$args['show_time_zone'] = 'off';
	}

	// Sanitize link_venue_website
	if ( ! isset( $args['link_venue_website'] ) OR 'on' != $args['link_venue_website'] ) {
		$args['link_venue_website'] = 'off';
	}
 
        return $args;
    }
 



    /* settings link in plugin management screen */
    function addmap_plugin_links($actions, $plugin_file ){

        if (false !== strpos($plugin_file, 'addmap')) {
            $actions['settings'] = '<a href="options-general.php?page=addmap">Settings</a>'; //options-general
        }

        return $actions;

    }

    function uninstall_addmap() {
            delete_option( 'addmap_options' );
    }


}	
