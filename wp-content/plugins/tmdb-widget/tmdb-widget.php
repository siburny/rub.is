<?php
/**
 * Plugin Name: TheMovieDB Widget
 * Description: Displays a dynamic list of movies/series based on the selected criteria.
 * Author: Maxim Rubis
 * Version: 0.1.0
 */

class TMDB_Widget extends WP_Widget
{

    const BASE_URL = 'https://api.themoviedb.org/3';

    /**
     * Register widget with WordPress.
     */
    public function __construct()
    {
        parent::__construct(
            'tmdb_widget', // Base ID
            esc_html__('TMDB Widget', 'text_domain'), // Name
            array('description' => esc_html__('Shows a list of movies/series', 'text_domain')) // Args
        );
    }

    private function get_url($id)
    {
        $args = array(
            'meta_key' => 'imdb',
            'meta_value' => $id,
        );
        $query = new WP_Query($args);

        if ($query->have_posts()) {
            return get_permalink($query->post);
        }

        return null;
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget($args, $instance)
    {
        $config = get_transient('tmdb_widget_configuration');
        if ($config === false) {
            $json = json_decode(file_get_contents('https://api.themoviedb.org/3/configuration?api_key=' . $instance['apiKey']));
            if (!empty($json) && !empty($json->images)) {
                $config = $json->images;
                set_transient('tmdb_widget_configuration', $config, WEEK_IN_SECONDS);
            } else {
                return '';
            }
        }
        //var_dump($config);

        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $res = $this->callApi(array('apiKey' => $instance['apiKey'], 'type' => $instance['type'], 'limit' => $instance['limit']));
        if (!empty($res) && count($res) > 0) {

            echo '<style>.tmdb-table td { border: none; }</style>';
            echo '<table class="tmdb-table">';
            echo '<tr>';

            foreach ($res as $index => $item) {
                if ($index > 0 && $index % $instance['columns'] == 0) {
                    echo '</tr><tr>';
                }

                echo '<td>';
                $url = $this->get_url($item->id);

                if (!empty($url)) {
                    echo '<a href="' . $url . '">';
                }
                echo ($index + 1) . '. ';
                echo ($item->title ? $item->title : $item->name);
                if (!empty($instance['thumbnail']) && !empty($item->poster_path)) {
                    echo '<br /><img src="' . $config->secure_base_url . 'w185' . $item->poster_path . '" alt="" style="height:100px;" />';
                }
                if (!empty($url)) {
                    echo '</a>';
                }
                echo '</td>';
            }

            echo '</tr>';
            echo '</table>';
        }

        //var_dump($res);

        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form($instance)
    {
        $apiKey = !empty($instance['apiKey']) ? $instance['apiKey'] : '';
        $title = !empty($instance['title']) ? $instance['title'] : '';
        $type = !empty($instance['type']) ? $instance['type'] : 1;
        $limit = !empty($instance['limit']) ? $instance['limit'] : 5;
        $thumbnail = !empty($instance['thumbnail']) ? $instance['thumbnail'] : 0;
        $columns = !empty($instance['columns']) ? $instance['columns'] : 2;
        ?>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('apiKey')); ?>"><?php esc_attr_e('API Key:', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('apiKey')); ?>" name="<?php echo esc_attr($this->get_field_name('apiKey')); ?>" type="text" value="<?php echo esc_attr($apiKey); ?>">
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_attr_e('Title:', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('type')); ?>"><?php esc_attr_e('Type:', 'text_domain');?></label>
	    	<select class="widefat" id="<?php echo esc_attr($this->get_field_id('type')); ?>" name="<?php echo esc_attr($this->get_field_name('type')); ?>">
				<option value="1" <?php echo $type == 1 ? 'selected' : ''; ?>>Popular movies</option>
				<option value="2" <?php echo $type == 2 ? 'selected' : ''; ?>>Top rated movies</option>
				<option value="3" <?php echo $type == 3 ? 'selected' : ''; ?>>Upcoming movies</option>
				<option value="4" <?php echo $type == 4 ? 'selected' : ''; ?>>Now playing movies</option>
				<option value="5" <?php echo $type == 5 ? 'selected' : ''; ?>>Popular TV shows</option>
				<option value="6" <?php echo $type == 6 ? 'selected' : ''; ?>>On the air TV shows</option>
				<option value="7" <?php echo $type == 7 ? 'selected' : ''; ?>>Airing today TV shows</option>
			</select>
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('limit')); ?>"><?php esc_attr_e('Number of items to display:', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('limit')); ?>" name="<?php echo esc_attr($this->get_field_name('limit')); ?>" type="text" value="<?php echo esc_attr($limit); ?>">
		</p>
		<p>
    		<label for="<?php echo esc_attr($this->get_field_id('thumbnail')); ?>"><?php esc_attr_e('Show thumbnail?', 'text_domain');?></label>
	    	<input class="widefat" id="<?php echo esc_attr($this->get_field_id('thumbnail')); ?>" name="<?php echo esc_attr($this->get_field_name('thumbnail')); ?>" type="checkbox" value="1" <?php echo !empty($thumbnail) ? 'checked="checked"' : ''; ?>>
		</p>
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('columns')); ?>"><?php esc_attr_e('Number of columns:', 'text_domain');?></label>
	    	<select class="widefat" id="<?php echo esc_attr($this->get_field_id('columns')); ?>" name="<?php echo esc_attr($this->get_field_name('columns')); ?>">
				<option value="1" <?php echo $columns == 1 ? 'selected' : ''; ?>>1</option>
				<option value="2" <?php echo $columns == 2 ? 'selected' : ''; ?>>2</option>
				<option value="3" <?php echo $columns == 3 ? 'selected' : ''; ?>>3</option>
				<option value="4" <?php echo $columns == 4 ? 'selected' : ''; ?>>4</option>
			</select>
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
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['apiKey'] = (!empty($new_instance['apiKey'])) ? sanitize_text_field($new_instance['apiKey']) : '';
        $instance['title'] = (!empty($new_instance['title'])) ? sanitize_text_field($new_instance['title']) : '';
        $instance['type'] = (!empty($new_instance['type'])) ? sanitize_text_field($new_instance['type']) : 1;
        $instance['limit'] = (!empty($new_instance['limit'])) ? sanitize_text_field($new_instance['limit']) : 5;
        $instance['thumbnail'] = (!empty($new_instance['thumbnail'])) ? 1 : 0;
        $instance['columns'] = (!empty($new_instance['columns'])) ? sanitize_text_field($new_instance['columns']) : 2;

        return $instance;
    }

    public function callApi($args)
    {
        if (empty($args['apiKey'])) {
            return array();
        }

        $args['type'] = !empty($args['type']) ? $args['type'] : 1;
        $args['limit'] = !empty($args['limit']) ? $args['limit'] : 5;

        $url = TMDB_Widget::BASE_URL;

        switch ($args['type']) {
            case 1:
                $url .= '/discover/movie?sort_by=popularity.desc&region=US';
                break;
            case 2:
                $url .= '/discover/movie?sort_by=vote_average.desc&&vote_count.gte=100&region=US';
                break;
            case 3:
                $url .= '/discover/movie?sort_by=popularity.desc&primary_release_date.gte=' . (new DateTime())->add(new DateInterval('P7D'))->format('Y-m-d') .
                '&primary_release_date.lte=' . (new DateTime())->add(new DateInterval('P14D'))->format('Y-m-d') . '&region=US';
                break;
            case 4:
                $url .= '/discover/movie?sort_by=popularity.desc&primary_release_date.lte=' . (new DateTime('now', new DateTimeZone(get_option('timezone_string'))))->format('Y-m-d') . '&primary_release_date.gte=' . (new DateTime())->sub(new DateInterval('P21D'))->format('Y-m-d') . '&region=US';
                break;
            case 5:
                $url .= '/discover/tv?sort_by=popularity.desc';
                break;
            case 6:
                $url .= '/discover/tv?sort_by=popularity.desc&language=en-US&timezone=America%2FNew_York&air_date.gte=' . (new DateTime('now', new DateTimeZone(get_option('timezone_string'))))->add(new DateInterval('P1D'))->format('Y-m-d');
                break;
            case 7:
                $url .= '/discover/tv?sort_by=popularity.desc&language=en-US&timezone=America%2FNew_York&air_date.gte=' . (new DateTime('now', new DateTimeZone(get_option('timezone_string'))))->format('Y-m-d') . '&air_date.lte=' . (new DateTime('now', new DateTimeZone(get_option('timezone_string'))))->format('Y-m-d');
                break;
            default:
                return array();
        }

        if (false) {

        } else {
            $json = json_decode(file_get_contents($url . '&api_key=' . $args['apiKey']));
            if (!empty($json) && is_object($json) && !empty($json->results) && is_array($json->results)) {
                return array_slice($json->results, 0, $args['limit']);
            } else {
                return array();
            }
        }
    }

} // class TMDB_Widget

function register_tmdb_widget()
{
    register_widget('TMDB_Widget');
}
add_action('widgets_init', 'register_tmdb_widget');
