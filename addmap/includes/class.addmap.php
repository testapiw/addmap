<?php

class Addmap {

        private static $month = array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
    
	private static $initiated = false;
	
	public static function init() {
            
		if ( ! self::$initiated ) {

                    self::init_hooks();
                        
		}
	}
	
       
	
/**
 * Initializes WordPress hooks
*/
private static function init_hooks() {
	self::$initiated = true;

	                   // динамические запросы AJAX
        if( defined('DOING_AJAX') && DOING_AJAX ){
                add_action( 'wp_ajax_get_lasttime3',        array('Addmap', 'get_lasttime3'));
                add_action( 'wp_ajax_nopriv_get_lasttime3', array('Addmap','get_lasttime3'));
        }  
        
        
        add_shortcode('whereabouts-addmap', array('Addmap', 'whereabouts_addmap_display_location'));
 

        add_action('wp_head', array('Addmap', 'js_variables'));
        
        Addmap::wp_scripts_load();
}


function get_lasttime3 () {
    
    $valid_req = check_ajax_referer( 'sstring', 'security', false );
    
    if ( false == $valid_req ) {
        wp_die( '-1' );
    }
    
    $data = self::getData();
    
    echo(json_encode( array('status'=>'ok','response'=>$data) ));
    
    unset($data);

    wp_die();
}

    /*
    createdAt
    shout
    
    venue
        name Oslo Sentrum
        location
            city :"Oslo"
            country: "Norway"
            state:"Oslo"
            lat:59.9128666971
            lng:10.7423968359
        
        posts
            count 1
            items [
               url     
            ]*/
    /*
    foreach ($options as $key => $value) {
        
        $name = str_replace('show_', '', $value);
        
        if (!empty($lc->$name)) {
            $data[$name] = $lc->$name;
        }
    }*/

private static function getData () {

    $location = get_option( 'whereabouts_swarm_current_location' );
    
    if (empty($location)) {return false;}
    
    $data = array();
    
    $options = get_option( 'addmap_options' );
    
    $lc = $location->venue->location;
    
    if (empty($lc)) {return false;}
    
 
    if (isset($options['show_venue']) && !empty($location->venue->name)) {
        $data['venue'] = strip_tags($location->venue->name);
    }
 
    if (isset($options['show_city']) && !empty($lc->city)) {
        $data['city'] = strip_tags($lc->city);
    }

    if (isset($options['show_country']) && !empty($lc->country)) {
        $data['country'] = strip_tags($lc->country);
    }    

    if (isset($options['show_state']) && !empty($lc->state)) {
        $data['state'] = strip_tags($lc->state);
    }
    
    if (!empty($lc->lat) && !empty($lc->lng)) {
        $data['gps'] = array('lat'=>(float)$lc->lat, 'lng'=>(float)$lc->lng);
    }
    
   
    if (isset($location->posts) && $location->posts->count > 0) {
        
        $data['href']  = strip_tags($location->posts->items[0]->url);
        $data['title'] = strip_tags($location->shout);
        $data['posts'] = 1;
                
    }
       
    $time = self::getTime_($location);
  
    if (!empty($time)) {
        
        $data['time'] = strip_tags($time);
        
    }
   
    return $data;
}


	
function whereabouts_addmap_display_location( $args ) {
    try {
    $data = self::getData();
    
    if (!empty( $location )) {return '';}
  
	$lc_output = array();
        
     
        if (isset($data['venue'])) {
                $lc_output[] = '<span class="venue">' . $data['venue'] . '</span>';
        }       
             
        if (isset($data['city'])) {
                $lc_output[] = '<span class="city">'  . $data['city'] . '</span>';
        }           

        if (isset($data['country'])) {
                $lc_output[] = '<span class="country">' . $data['country'] . '</span>';
        }           
        
        if (isset($data['state']) && strcmp($data['state'], $data['city']) !==0 ) {
                $lc_output[] = '<span class="state">'   . $data['state'] . '</span>';
        }         

        $info = implode( ', ', $lc_output );
        
	$info .= '';
        
        $info .= self::createlink($data);

        $attr = (isset($data['gps']) ) 
                ? ' data-lat="' . $data['gps']['lat']. '"'
                . ' data-lng="' . $data['gps']['lng']. '"' : '';
        
        $output = '<div class="addmap"><p class="info_location">'.$info.'</p>'
                . '<div id="gmap" '.$attr.'></div></div>';
        
        $output = apply_filters( 'addmap-output', $output, $data );
    
        unset ($data);        
        
        return  $output; 
    } 
    catch (ErrorException $e) {
        
        return '';
        
    }
}


/*
 * link - Последнее обновление:
 */
private static function createlink(& $data) {
    try {

        if (isset($data['time'])) {
            $time = $data['time'];

            $lastupd = 'Последнее обновление: '. $time;
        
            $link = ($data['posts'] === 1) 
                     ? '<a href="'.$data['href'].'" title="'.$data['title'].'" target="_blank">'.$lastupd.'<a/>'
                     : $lastupd;
        // add class
            return '<p class="lastupd">'.$link.'</p>';
            
        }
        
        return false;
        
    } catch(ErrorException $e){
        
        return false;
        
    }
}


/* 
 *  Последнее обновление:
 *      createdAt
 */
private static function getTime_ (&$lc) {
    try {     
        if (empty($lc)) {return false;}
    
        $fulltime = (int)$lc->createdAt +(int)$lc->timeZoneOffset + 10800; //+3
    
        $month = self::$month[date("m", $fulltime)-1];
    
        return date("j $month H:i", $fulltime) . " UTC+3";
        
    } catch (ErrorException $e) {
        return false;
    }    
}



function wp_scripts_load()  
{  
        wp_enqueue_style( 'addmap-style', ADDMAP_ASSETS . 'css/addmap.css', array() );  
        
        wp_enqueue_script( 'addmap-script', ADDMAP_ASSETS . 'js/addmap2.js', array( 'jquery' ), "3.4.0" ); 
}

/*
 * settings: google API | language, key|
 */
function js_variables(){
    
    $options = get_option( 'addmap_options' );
    
    $key = (!empty($options['key_google']))?$options['key_google']:"";
    
    $ajax_nonce = wp_create_nonce("sstring");
    
    $variables = array (
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('myajax-nonce'),
        's' => $ajax_nonce,
        'gkey'  => $key,
        'glang' => 'ru'
    );
    
    echo( '<script type="text/javascript">window.wp_data = '
            . json_encode($variables). ';</script>' );
            
}


//add_action( 'whereabouts_swarm_fetch_location', 'our func' );
}