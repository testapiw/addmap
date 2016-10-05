<?php
class Addmap_Widget extends WP_Widget
{
    public function __construct() {

        $widget_slug = 'Addmap_Widget';

        parent::__construct( $widget_slug, 'Whereabouts: Addmap', array( 'description' =>  __( 'Shows your current location on map.', 'whereabouts-addmap' ) ));
        
    }
    
    
    
    
    function widget( $args, $instance ) {

        $title = apply_filters( 'widget_title', $instance['title'] );

        // Echo widget
        echo $args['before_widget'];
        if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        
        echo Addmap::whereabouts_addmap_display_location( NULL );
        
        echo $args['after_widget'];

    }

	// Save widget options    
    function update( $new_instance, $old_instance ) {

        $instance = array();
        $instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;

    }

    // Output admin widget options form
    function form( $instance ) {

        // Set title variable, if it is not saved
        if ( isset( $instance['title'] ) ) {
            $title = $instance['title'];
        }
        else {
            $title = '';
        }

        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label> 
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php
    }
}

function addmap_register_widgets() {
	register_widget( 'Addmap_Widget' );
}

add_action( 'widgets_init', 'addmap_register_widgets' );

