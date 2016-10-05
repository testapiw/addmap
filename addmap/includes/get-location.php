<?php
/**
 * Setup the cron job
 *
 * @package whereabouts-swarm
 * @since 0.1.0
 */
// регистрируем пятиминутный интервал
/*add_filter( 'cron_schedules', 'cron_add_five_min' );
function cron_add_five_min( $schedules ) {
	$schedules['five_min'] = array(
		'interval' => 60 * 5,
		'display' => 'Раз в 5 минут'
	);
	return $schedules;
}*/


function whereabouts_addmap_setup_schedule() {

    if ( ! wp_next_scheduled( 'whereabouts_addmap_fetch_location' ) ) {
        wp_schedule_event( time(), 'hourly', 'whereabouts_addmap_fetch_location');//hourly  five_min
    }
}

add_action( 'wp', 'whereabouts_addmap_setup_schedule' );


/**
 * On the scheduled action hook, fetch the location from foursquare
 *
 * @package whereabouts-swarm
 * @since 0.1.0
 */
function whereabouts_addmap_fetch_location_go() {

    // Get auth code
	$auth_code = get_option( 'whereabouts_swarm_auth_code' );

    // Check if file path is set and exists
    if ( isset( $auth_code ) AND ! empty( $auth_code ) ) {

		$url = 'https://api.foursquare.com/v2/users/self/checkins?oauth_token='.$auth_code.'&v=20140806&locale=en&limit=1';

		$response = wp_remote_get( $url );

		if ( is_wp_error( $response ) ) {

			$error_messages = $response->get_error_messages();
			foreach( $error_messages as $message ) {
				echo '<span class="error">' . implode( $error_messages, '<br />' ) . '<br />Maybe the <a href="http://where.abouts.io/faq/">FAQs</a> are helpful?</span>';
			}

		}
		else {
                    
                   	$obj = json_decode( $response['body'] );

			if ( isset( $obj->meta->code ) AND $obj->meta->code == 200 ) {

				$current_location = $obj->response->checkins->items[0];

				update_option( 'whereabouts_swarm_current_location', $current_location );

				return '<span class="updated">' . __( 'You current location was updated successfully.', 'whereabouts-swarm' ) . '</span>';
			}
			else {
				return '<span class="error">' . __( '<strong>Error:</strong> The data received from Swarm could not be processed. Please check our <a href="https://where.abouts.io/faq">FAQs</a> for details. ', 'whereabouts-swarm' ) . '</span>';
			}
		}
	}
}

add_action( 'whereabouts_addmap_fetch_location', 'whereabouts_addmap_fetch_location_go' );