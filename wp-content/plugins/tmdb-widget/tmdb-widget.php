<?php
/**
 * Plugin Name: TheMovieDB Widget
 * Description: Displays a dynamic list of movies/series based on the selected criteria.
 * Author: Maxim Rubis
 * Version: 0.1.0
 */
 
 class TMDB_Widget extends WP_Widget {

    const BASE_URL = 'https://api.themoviedb.org/3';

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'tmdb_widget', // Base ID
			esc_html__( 'TMDB Widget', 'text_domain' ), // Name
			array( 'description' => esc_html__( 'Shows a list of movies/series', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}
        
        $res = $this->callApi(array('apiKey' => $instance['apiKey'],'type' => 1));
        var_dump($res);


		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$apiKey = ! empty( $instance['apiKey'] ) ? $instance['apiKey'] : '';
		$title = ! empty( $instance['title'] ) ? $instance['title'] : '';
		?>
		<p>
    		<label for="<?php echo esc_attr( $this->get_field_id( 'apiKey' ) ); ?>"><?php esc_attr_e( 'API Key:', 'text_domain' ); ?></label> 
	    	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'apiKey' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'apiKey' ) ); ?>" type="text" value="<?php echo esc_attr( $apiKey ); ?>">
		</p>
		<p>
    		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'text_domain' ); ?></label> 
	    	<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php 
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['apiKey'] = ( ! empty( $new_instance['apiKey'] ) ) ? sanitize_text_field( $new_instance['apiKey'] ) : '';
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';

		return $instance;
    }
    
    function callApi($args)
    {
        if(empty($args['apiKey']))
        {
            return array();
        }

        $args['type'] = !empty($args['type']) ? $args['type'] : 1;
        $args['count'] = !empty($args['count']) ? $args['count'] : 5;

        $url = TMDB_Widget::BASE_URL;
        
        switch($args['type'])
        {
            case 1:
                $url .= '/discover/movie?sort_by=popularity.desc';
                break;
            default:
                return array();
        }

        if(false)
        {
        
        }
        else
        {
            $json = json_decode(file_get_contents($url.'&api_key='.$args['apiKey']));
            if(!empty($json) && is_object($json) && !empty($json->results) && is_array($json->results))
            {
                return array_slice($json->results, 0, $args['count']);
            }
            else
            {
                return array();
            }
        }
    }

} // class TMDB_Widget

function register_tmdb_widget() {
    register_widget( 'TMDB_Widget' );
}
add_action( 'widgets_init', 'register_tmdb_widget' );
